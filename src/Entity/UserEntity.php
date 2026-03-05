<?php

namespace App\Entity;

use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Doctrine\ORM\Mapping as ORM;
use App\Config\Constants;
use App\Repository\UserRepository;
use DateTimeImmutable;
use Gedmo\Mapping\Annotation as Gedmo;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[Gedmo\SoftDeleteable(fieldName: 'deletedAt', timeAware: false)]
#[ORM\Table(name: Constants::TABLE_USER)]
class UserEntity extends BaseEntity implements UserInterface, PasswordAuthenticatedUserInterface
{
  #[ORM\Column(type: 'string', length: 255)]
  private string $email;

  #[ORM\Column(type: 'json')]
  private array $roles = [];

  #[ORM\Column(type: 'string', nullable: true)]
  private ?string $password  = null;

  #[ORM\Column(type: 'string', length: 255)]
  private string $firstName;

  #[ORM\Column(type: 'string', length: 255)]
  private string $lastName;

  #[ORM\Column(type: 'string', length: 255, nullable: true)]
  private ?string $middleName = null;

  // #[ORM\Column(type: 'string', length: 12, nullable: true)]
  // private ?string $phone  = null;

  #[ORM\Column(type: 'string', length: 255, nullable: true)]
  private ?string $googleId  = null;

  #[ORM\Column(type: 'datetime_immutable', nullable: true)]
  private ?DateTimeImmutable $emailVerifiedAt = null;

  #[ORM\Column(type: 'datetime', nullable: true)]
  private ?\DateTimeInterface $deletedAt = null;

  public function getEmail(): string
  {
    return $this->email;
  }

  public function setEmail(string $email): self
  {
    $this->email = $email;

    return $this;
  }

  public function setFirstName(string $firstName): self
  {
    $this->firstName = $firstName;

    return $this;
  }

  public function getFirstName()
  {
    return $this->firstName;
  }

  public function setLastName(string $lastName): self
  {
    $this->lastName = $lastName;

    return $this;
  }

  public function getLastName()
  {
    return $this->lastName;
  }

  public function setMiddleName(string $middleName): self
  {
    $this->middleName = empty($middleName) ? null : $middleName;

    return $this;
  }

  public function getMiddleName()
  {
    return $this->middleName;
  }

  // public function getPhone()
  // {
  //   return $this->phone;
  // }

  // public function setPhone(string $phone): self
  // {
  //   $this->phone = empty($phone) ? null : $phone;

  //   return $this;
  // }

  public function getGoogleId()
  {
    return $this->googleId;
  }

  public function setGoogleId(string $googleId): self
  {
    $this->googleId = empty($googleId) ? null : $googleId;

    return $this;
  }

  public function setEmailVerifiedAt(DateTimeImmutable $emailVerifiedAt): self
  {
    $this->emailVerifiedAt = $emailVerifiedAt;

    return $this;
  }

  public function getEmailVerifiedAt()
  {
    return $this->emailVerifiedAt;
  }


  /**
   * The public representation of the user (e.g. a username, an email address, etc.)
   *
   * @see UserInterface
   */
  public function getUserIdentifier(): string
  {
    return (string) $this->email;
  }

  /**
   * @see UserInterface
   */
  public function getRoles(): array
  {
    $roles = $this->roles;
    // Guarantee every user at least has ROLE_USER
    $roles[] = Constants::ROLE_USER;

    return array_unique($roles);
  }

  public function setRoles(array $roles): self
  {
    $this->roles = $roles;

    return $this;
  }

  /**
   * @see PasswordAuthenticatedUserInterface
   */
  public function getPassword(): ?string
  {
    return $this->password;
  }

  public function setPassword(?string $password): self
  {
    $this->password = empty($password) ? null : password_hash($password, PASSWORD_BCRYPT, [
      'cost' => Constants::BCRYPT_COST,
    ]);

    return $this;
  }

  public function comparePassword(string $plainPassword): bool
  {
    return password_verify($plainPassword, $this->password);
  }

  /**
   * @see UserInterface
   */
  public function eraseCredentials(): void
  {
    // If you store any temporary, sensitive data on the user, clear it here
    // $this->plainPassword = null;
  }

  public function getUserFullName(): string
  {
    if (!empty($this->middleName)) {
      return trim($this->lastName . ' ' . $this->middleName . ' ' . $this->firstName);
    }

    return trim($this->lastName . ' ' . $this->firstName);
  }

  public function setDeletedAt(?\DateTime $deletedAt): static
  {
      $this->deletedAt = $deletedAt;

      return $this;
  }
}
