<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use JMS\Serializer\Annotation as Serializer;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\AuthorRepository")
 * @ORM\Table(name="authors")
 */
class Author
{
    use TimestampableEntity;

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @Assert\NotBlank()
     * @Assert\Length(min="1", max="255")
     * @ORM\Column(type="string", length=255)
     */
    private $firstName;

    /**
     * @Assert\NotBlank()
     * @Assert\Length(min="1", max="255")
     * @ORM\Column(type="string", length=255)
     */
    private $lastName;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $secondName;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Book", mappedBy="author")
     * @Serializer\Exclude()
     */
    private $books;

    /**
     * @var int
     * @ORM\Column(type="integer")
     */
    private $countBooks = 0;

    public function __construct()
    {
        $this->books = new ArrayCollection();
    }

    public function __toString(): string
    {
        return $this->getFio();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(string $firstName): self
    {
        $this->firstName = $firstName;

        return $this;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(string $lastName): self
    {
        $this->lastName = $lastName;

        return $this;
    }

    public function getSecondName(): ?string
    {
        return $this->secondName;
    }

    public function setSecondName(?string $secondName): self
    {
        $this->secondName = $secondName;

        return $this;
    }

    /**
     * @return Collection|Book[]
     */
    public function getBooks(): Collection
    {
        return $this->books;
    }

    public function addBook(Book $book): self
    {
        if (!$this->books->contains($book)) {
            $this->books[] = $book;
            $book->setAuthor($this);
        }

        return $this;
    }

    public function removeBook(Book $book): self
    {
        if ($this->books->contains($book)) {
            $this->books->removeElement($book);
            // set the owning side to null (unless already changed)
            if ($book->getAuthor() === $this) {
                $book->setAuthor(null);
            }
        }

        return $this;
    }

    public function getFio(): string
    {
        return $this->firstName.' '.$this->lastName.' '.$this->secondName;
    }

    public function getCountBooks(): int
    {
        return $this->countBooks;
    }

    public function setCountBooks(int $countBooks): self
    {
        $this->countBooks = $countBooks;

        return $this;
    }
}
