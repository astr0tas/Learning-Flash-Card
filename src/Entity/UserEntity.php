<?php

namespace App\Entity;

use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Doctrine\ORM\Mapping as ORM;
use App\Config\Constants;
use App\Repository\UserRepository;

#[ORM\Entity(repositoryClass: UserRepository::class)]
class UserEntity extends BaseEntity implements UserInterface, PasswordAuthenticatedUserInterface
{
  #[ORM\Column(type: 'string', length: 255, unique: true)]
  private string $email;

  #[ORM\Column(type: 'json')]
  private array $roles = [];

  #[ORM\Column(type: 'string')]
  private string $password;

  #[ORM\Column(type: 'string', length: 255)]
  protected string $firstName;

  #[ORM\Column(type: 'string', length: 255)]
  protected string $lastName;

  #[ORM\Column(type: 'string', length: 255, nullable: true)]
  protected ?string $middleName = null;

  #[ORM\Column(type: 'string', length: 12, nullable: true)]
  protected string $phone;

  public function getEmail(): string
  {
    return $this->email;
  }

  public function setEmail(string $email): self
  {
    $this->email = $email;

    return $this;
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
    // guarantee every user at least has ROLE_USER
    $roles[] = Constants::DEFAULT_ROLE;

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
  public function getPassword(): string
  {
    return $this->password;
  }

  public function setPassword(string $password): self
  {
    $this->password = $password;

    return $this;
  }

  /**
   * @see UserInterface
   */
  public function eraseCredentials(): void
  {
    // If you store any temporary, sensitive data on the user, clear it here
    // $this->plainPassword = null;
  }
}
