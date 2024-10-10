<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241010163327 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE game_platform_game (game_platform_id INT NOT NULL, game_id INT NOT NULL, INDEX IDX_DC7CACB21B30B6D (game_platform_id), INDEX IDX_DC7CACBE48FD905 (game_id), PRIMARY KEY(game_platform_id, game_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE game_platform_game ADD CONSTRAINT FK_DC7CACB21B30B6D FOREIGN KEY (game_platform_id) REFERENCES game_platform (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE game_platform_game ADD CONSTRAINT FK_DC7CACBE48FD905 FOREIGN KEY (game_id) REFERENCES game (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE game_platform_game DROP FOREIGN KEY FK_DC7CACB21B30B6D');
        $this->addSql('ALTER TABLE game_platform_game DROP FOREIGN KEY FK_DC7CACBE48FD905');
        $this->addSql('DROP TABLE game_platform_game');
    }
}
