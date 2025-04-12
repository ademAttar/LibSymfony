<?php

namespace App\Entity;


use App\Repository\BookRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: BookRepository::class)]
class Book
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: "string", length: 255)]
    private ?string $title = null;

    #[ORM\Column(type: "string", length: 255)]
    private ?string $author = null;

    #[ORM\Column(type: "integer")]
    private ?int $publishedYear = null;

    #[ORM\Column(type: "text")]
    private ?string $description = null;

    #[ORM\Column(type: "string", length: 255, nullable: true)]
    private ?string $image = null;

    public function __construct(?string $title = null, ?string $author = null, ?string $description = null, ?int $publishedYear = null, ?string $image = null)
    {
        $this->title = $title;
        $this->author = $author;
        $this->publishedYear = $publishedYear;
        $this->description = $description;
        $this->image = $image;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;
        return $this;
    }

    public function getAuthor(): ?string
    {
        return $this->author;
    }

    public function setAuthor(string $author): self
    {
        $this->author = $author;
        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;
        return $this;
    }

    public function getPublishedYear(): ?int
    {
        return $this->publishedYear;
    }

    public function setPublishedYear(int $publishedYear): self
    {
        $this->publishedYear = $publishedYear;
        return $this;
    }

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(?string $image): self
    {
        $this->image = $image;
        return $this;
    }
}
