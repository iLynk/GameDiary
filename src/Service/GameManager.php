<?php

namespace App\Service;

use App\Entity\Game;
use App\Entity\GameCategory;
use Doctrine\ORM\EntityManagerInterface;

class GameManager
{
private EntityManagerInterface $entityManager;

public function __construct(EntityManagerInterface $em)
{
$this->entityManager = $em;
}

public function saveGame(array $gameData): Game
{
// Assume $gameData contains relevant game information

$game = new Game();
$game->setName($gameData['name']);
$game->setReleaseDate(new \DateTime($gameData['release_dates'][0]['date'])); // Example date handling

// Manage categories
$categoryName = $gameData['category'] ?? 'Unknown';
$category = $this->findOrCreateCategory($categoryName);
$game->setCategory($category);

$this->entityManager->persist($game);
$this->entityManager->flush();

return $game;
}

private function findOrCreateCategory(string $categoryName): GameCategory
{
$category = $this->entityManager->getRepository(GameCategory::class)
->findOneBy(['name' => $categoryName]);

if (!$category) {
$category = new GameCategory();
$category->setName($categoryName);
$this->entityManager->persist($category);
}

return $category;
}
}
