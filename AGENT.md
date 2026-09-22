# AGENT.md

Reference doc for coding agents working in this repo. Read this instead of re-deriving the project structure from scratch each session.

## What this is

A Symfony 7.3 web app for storing and organizing flashcards ("Learning Flash Card"). Cards live inside **Topics**, which can be nested (a topic can contain child topics and/or cards), similar to folders. Users authenticate via form login or Google OAuth2.

## Tech stack

- **Backend:** PHP 8.2+, Symfony 7.3, Doctrine ORM 3.x / DBAL 3.x, Doctrine Migrations
- **Soft delete:** `stof/doctrine-extensions-bundle` (Gedmo `SoftDeleteable`) — entities are soft-deleted (`deletedAt`) and moved to a trash view instead of being hard-deleted immediately
- **Frontend:** Symfony UX Twig Components (`symfony/ux-twig-component`), Alpine.js (via Stimulus/AssetMapper, no Node bundler), Tailwind CSS (`symfonycasts/tailwind-bundle`), Symfony AssetMapper (no Webpack/Vite)
- **Auth:** `symfony/security-bundle` form login + `league/oauth2-google`
- **i18n:** Symfony Translation, ICU message format (`messages+intl-icu.{en,vi}.yaml`), English and Vietnamese
- **Tests:** PHPUnit (scaffold only — `tests/` currently has no real test cases, just `bootstrap.php`)

No `npm`/frontend build step is required for local dev — AssetMapper serves `assets/` directly. Composer's `post-install-cmd`/`post-update-cmd` run `cache:clear`, `assets:install`, `importmap:install`.

## Data model

```
UserEntity
 ├── topicEntities: TopicEntity[]   (OneToMany, mappedBy userEntity)
 └── cardEntities:  CardEntity[]    (OneToMany, mappedBy userEntity)

TopicEntity (table: topic_tbl)
 ├── userEntity:            UserEntity           (ManyToOne, owning)
 ├── parentTopicEntity:     ?TopicEntity          (ManyToOne, self-referencing, owning)
 ├── childrenTopicEntities: TopicEntity[]         (OneToMany, mappedBy parentTopicEntity)
 ├── cardEntities:          CardEntity[]          (OneToMany, mappedBy topicEntity, cascade remove)
 └── name, restorePath, deletedAt

CardEntity (table: card_tbl)
 ├── userEntity:  UserEntity   (ManyToOne, owning)
 ├── topicEntity: ?TopicEntity (ManyToOne, owning — null means card sits at the root, no topic)
 └── title, subtitle, description, cardType, cardColor, cardTextColor, restorePath, deletedAt
```

A **topic can contain other topics and/or cards** (self-referencing tree). A card with `topicEntity = null` lives at the root level. Soft-deleted rows keep a `restorePath` (a `/`-joined breadcrumb of ancestor topic names) so they can be restored back into the tree later even if the original parent was also deleted — see `RestoreNode` / `TrashService::restoreTopic()` / `restoreCard()`.

All entities extend `BaseEntity` (`id`, `createdAt`, `updatedAt` with lifecycle callbacks).

## Directory layout (`src/`)

```
src/
├── Config/           Static constant classes used instead of magic strings/hardcoded values
│   ├── Constants.php     App-wide constants (table names, flash-card types, session keys, ...)
│   ├── Constraints.php   Field length limits (validated both here and at the ORM column level)
│   ├── Routes.php        Every route path + route name as pairs of consts (*_ROUTE_URL / *_ROUTE_NAME)
│   ├── TwigTemplate.php  Every renderable Twig view path as a const (PAGE_*)
│   ├── ContentType.php, Header.php  HTTP header/content-type constants
├── Controller/
│   ├── BaseController.php        Adds $translator, $session, getFlashBag() to AbstractController
│   ├── User/                     TopicController, TrashController, HomeController — end-user pages
│   ├── Admin/                    HomeController — admin area
│   ├── Api/                      BaseApiController; Api/User/TopicApiController (JSON endpoints)
│   └── AuthenticationController.php, LocaleController.php
├── Entity/            Doctrine entities (see data model above); BaseEntity is the common parent
├── Repository/        One repository per entity, extends BaseRepository (ServiceEntityRepository + $entityManager)
├── Service/           Business logic, one per feature area; extends BaseService (session/user/entityManager access, soft-delete filter toggling)
│   ├── TopicService.php    CRUD + tree/breadcrumb logic for topics and cards
│   ├── TrashService.php    Soft-deleted listing, permanent delete, restore
│   ├── AuthenticationService.php, EmailService.php
├── DTO/               Plain objects the ClassUtility maps request data onto (see "Request → DTO mapping" below)
├── ToolClass/
│   └── RestoreNode.php    In-memory tree node used while rebuilding topic/card hierarchy on restore
├── Twig/
│   ├── Components/    PHP classes backing `<twig:X ... />` components (paired 1:1 with templates/components/X.html.twig)
│   └── Extension/, Runtime/   Custom Twig functions/filters
├── EventSubscriber/   CSRF validation, locale switching, method-not-allowed handling
└── Utility/           ClassUtility (DTO⇄array mapping via reflection), Utility, Logger
```

### Request → DTO mapping convention

`ClassUtility::mapArrayToDTO($postData, $dto)` maps `$_POST`/`$_GET` keys to DTO setters by **camelCase-matching** the key against each property name (via `Symfony\Component\String\u()->camel()`). This means a `snake_case` form field like `new_topic_name` auto-matches a DTO property `newTopicName` — **the property name is the contract**, not the setter name literally. Keep form `name="..."` attributes in templates in sync with DTO property names when adding/renaming fields.

`ClassUtility::validateInputDTO()` round-trips the DTO back through `mapDTOtoArray()` and validates it against `Assert\Collection`-style field constraints, then maps validation errors back to the *original* input keys (so error arrays match your form field names, not the camelCase property names).

### Twig Components

Each `src/Twig/Components/X.php` (with `#[AsTwigComponent]`) pairs with `templates/components/X.html.twig` of the **same name** — this pairing is implicit (Symfony UX convention), not visible via imports, so renaming one requires renaming the other. Current components: `Topic`, `Breadcrumb`, `Button`, `ClickInput`, `Dropdown`, `Form`, `Loading`, `Modal`, `NoContent`, `AppJsScript`, `AppSetting`, plus the field components `Input`, `Password`, `Search`, `Textarea` and `Select`, whose classes point at `templates/components/fields/X.html.twig` through the attribute's `template:` argument.

### Frontend (Alpine.js) pairing

Page-specific Alpine logic lives in `assets/scripts/templates/views/<same path as the twig template>/index.js`, loaded via `<script src="{{ asset('scripts/templates/views/.../index.js') }}">` at the bottom of the matching `templates/views/.../index.html.twig`. E.g. `templates/views/user/topic/index.html.twig` ↔ `assets/scripts/templates/views/user/topic/index.js`.

## Routing / translation / template constants

Never hardcode a URL, route name, translation key path, or `.html.twig` path in a controller/template — go through the `Routes`, `TwigTemplate`, and `messages+intl-icu.*.yaml` files respectively, referenced via `constant('App\\Config\\Routes::X_ROUTE_URL')` in Twig or `Routes::X_ROUTE_URL` in PHP. This is how `Routes::TOPIC_ROUTE_URL`, `TwigTemplate::PAGE_USER_TOPIC`, etc. are used throughout.

## Important: `FlashBag` ≠ topic/card "bag"

Symfony's session `FlashBagInterface` / `getFlashBag()` / `FlashBagAwareSessionInterface` (used for one-request flash messages, e.g. form validation errors surviving a redirect) is **unrelated** to the flashcard/topic domain despite the similar name. Do not conflate the two when searching/renaming.

## Config files worth knowing

- `config/packages/security.yaml` — form_login + remember_me, access control: `PUBLIC_ACCESS` for auth pages, `ROLE_ADMIN` for `/admin`, `ROLE_USER` for everything else under `/`
- `config/packages/stof_doctrine_extensions.yaml` — enables the Gedmo SoftDeleteable listener
- `config/packages/asset_mapper.yaml`, `importmap.php` — AssetMapper (no bundler) config
- `config/packages/twig_component.yaml` — Twig Components config
- `migrations/` — Doctrine migrations; `Version20260920183832` is the initial schema baseline. Use `doctrine:migrations:diff` + `migrate` for schema changes, not `doctrine:schema:update`.

## Common commands

```bash
php bin/console debug:router          # list all routes
php bin/console lint:twig templates/  # validate Twig syntax
php bin/console doctrine:schema:validate --skip-sync   # validate ORM mapping
php bin/console lint:yaml translations/
composer install                      # also runs assets:install + importmap:install
```
