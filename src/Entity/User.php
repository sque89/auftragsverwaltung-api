<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Table(name="user")
 * @ORM\Entity(repositoryClass="App\Repository\UserRepository")
 */
class User implements UserInterface, \Serializable {

    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Groups({"api", "unsensitive"})
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=25, unique=true)
     * @Groups({"api", "unsensitive"})
     */
    private $username;

    /**
     * @ORM\Column(type="string", length=64)
     */
    private $password;

    /**
     * @ORM\Column(type="string", length=50)
     * @Groups({"api", "unsensitive"})
     */
    private $firstname;

    /**
     * @ORM\Column(type="string", length=50)
     * @Groups({"api", "unsensitive"})
     */
    private $lastname;

    /**
     * @ORM\Column(type="string", length=60, unique=true)
     * @Groups({"api"})
     */
    private $email;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @Groups({"api"})
     */
    private $settings;

    /**
     * @ORM\Column(name="is_active", type="boolean")
     * @Groups({"api", "unsensitive"})
     */
    private $isActive;

    /**
     * Many Users have Many Roles.
     * @ORM\ManyToMany(targetEntity="Role")
     * @ORM\JoinColumn(referencedColumnName="id")
     * @Groups({"api"})
     */
    private $roles;

    /**
     * Many Users have Many Jobs.
     * @ORM\ManyToMany(targetEntity="Job", mappedBy="arrangers")
     */
    private $jobs;

    public function __construct() {
        $this->jobs = new \Doctrine\Common\Collections\ArrayCollection();
        $this->isActive = true;
        // may not be needed, see section on salt below
        // $this->salt = md5(uniqid('', true));
    }

    public function getId() {
        return $this->id;
    }

    public function getFirstname() {
        return $this->firstname;
    }

    public function getLastname() {
        return $this->lastname;
    }

    public function getEmail() {
        return $this->email;
    }

    public function getSettings(): ?string {
        return $this->settings;
    }

    public function getIsActive() {
        return $this->isActive;
    }

    public function setId($id) {
        $this->id = $id;
    }

    public function setFirstname($firstname) {
        $this->firstname = $firstname;
    }

    public function setLastname($lastname) {
        $this->lastname = $lastname;
    }

    public function setEmail($email) {
        $this->email = $email;
    }

    public function setSettings(string $settings) {
        $this->settings = $settings;
    }

    public function setIsActive($isActive) {
        $this->isActive = $isActive;
    }

    public function setUsername($username) {
        $this->username = $username;
    }

    public function getUsername() {
        return $this->username;
    }

    public function getSalt() {
        // you *may* need a real salt depending on your encoder
        // see section on salt below
        return null;
    }

    public function setPassword($password) {
        $this->password = $password;
    }

    public function getPassword() {
        return $this->password;
    }

    public function getRoles() {
        $rolesOfUser = array();
        foreach ($this->roles as $key => $value) {
            $rolesOfUser[] = $value->getName();
        }
        return $rolesOfUser;
    }

    public function setRoles($roles) {
        $this->roles = $roles;
    }

    public function eraseCredentials() {
    }

    public function isAdministrator() {
        return is_integer(array_search('ROLE_ADMIN', $this->getRoles()));
    }

    /** @see \Serializable::serialize() */
    public function serialize() {
        return serialize(array(
            $this->id,
            $this->username,
            $this->password,
                // see section on salt below
                // $this->salt,
        ));
    }

    /** @see \Serializable::unserialize() */
    public function unserialize($serialized) {
        list (
                $this->id,
                $this->username,
                $this->password,
                // see section on salt below
                // $this->salt
                ) = unserialize($serialized);
    }
}
