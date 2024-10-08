<?php

namespace App\Entity;

use App\Repository\GameRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: GameRepository::class)]
class Game
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(length: 255)]
    private ?string $slug = null;

    #[ORM\Column(length: 255)]
    private ?string $releaseDate = null;

    #[ORM\Column(length: 255)]
    private ?string $cover = null;

    #[ORM\Column(type: Types::JSON)]
    private array $platform = [];

    #[ORM\Column(type: Types::JSON)]
    private array $editor = [];

    /**
     * @var Collection<int, Review>
     */
    #[ORM\OneToMany(targetEntity: Review::class, mappedBy: 'game', orphanRemoval: true)]
    private Collection $reviews;

    /**
     * @var Collection<int, UserList>
     */
    #[ORM\ManyToMany(targetEntity: UserList::class, mappedBy: 'games')]
    private Collection $userLists;

    /**
     * @var Collection<int, GameCategory>
     */
    #[ORM\ManyToMany(targetEntity: GameCategory::class, mappedBy: 'game')]
    private Collection $gameCategories;

    #[ORM\Column]
    private ?int $apiId = null;

    public function __construct()
    {
        $this->reviews = new ArrayCollection();
        $this->userLists = new ArrayCollection();
        $this->gameCategories = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): static
    {
        $this->slug = $slug;

        return $this;
    }

    public function getReleaseDate(): ?string
    {
        return $this->releaseDate;
    }

    public function setReleaseDate(string $releaseDate): static
    {
        $this->releaseDate = $releaseDate;

        return $this;
    }

    public function getCover(): ?string
    {
        return $this->cover;
    }

    public function setCover(string $cover): static
    {
        $this->cover = $cover;

        return $this;
    }

    public function getPlatform(): array
    {
        return $this->platform;
    }

    public function setPlatform(array $platform): static
    {
        $this->platform = $platform;

        return $this;
    }

    public function getEditor(): array
    {
        return $this->editor;
    }

    public function setEditor(array $editor): static
    {
        $this->editor = $editor;

        return $this;
    }

    /**
     * @return Collection<int, Review>
     */
    public function getReviews(): Collection
    {
        return $this->reviews;
    }

    public function addReview(Review $review): static
    {
        if (!$this->reviews->contains($review)) {
            $this->reviews->add($review);
            $review->setGame($this);
        }

        return $this;
    }

    public function removeReview(Review $review): static
    {
        if ($this->reviews->removeElement($review)) {
            // set the owning side to null (unless already changed)
            if ($review->getGame() === $this) {
                $review->setGame(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, UserList>
     */
    public function getUserLists(): Collection
    {
        return $this->userLists;
    }

    public function addUserList(UserList $userList): static
    {
        if (!$this->userLists->contains($userList)) {
            $this->userLists->add($userList);
            $userList->addGame($this);
        }

        return $this;
    }

    public function removeUserList(UserList $userList): static
    {
        if ($this->userLists->removeElement($userList)) {
            $userList->removeGame($this);
        }

        return $this;
    }

    /**
     * @return Collection<int, GameCategory>
     */
    public function getGameCategories(): Collection
    {
        return $this->gameCategories;
    }

    public function addGameCategory(GameCategory $gameCategory): static
    {
        if (!$this->gameCategories->contains($gameCategory)) {
            $this->gameCategories->add($gameCategory);
            $gameCategory->addGame($this);
        }

        return $this;
    }

    public function removeGameCategory(GameCategory $gameCategory): static
    {
        if ($this->gameCategories->removeElement($gameCategory)) {
            $gameCategory->removeGame($this);
        }

        return $this;
    }

    public function getApiId(): ?int
    {
        return $this->apiId;
    }

    public function setApiId(int $apiId): static
    {
        $this->apiId = $apiId;

        return $this;
    }
}
