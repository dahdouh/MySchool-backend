<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200426133459 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE answer (id INT AUTO_INCREMENT NOT NULL, student_id INT NOT NULL, question_id INT NOT NULL, choice_id INT NOT NULL, quiz_attempt_id INT NOT NULL, INDEX IDX_DADD4A25CB944F1A (student_id), INDEX IDX_DADD4A251E27F6BF (question_id), INDEX IDX_DADD4A25998666D1 (choice_id), INDEX IDX_DADD4A25F8FE9957 (quiz_attempt_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE choice (id INT AUTO_INCREMENT NOT NULL, question_id INT NOT NULL, content VARCHAR(255) NOT NULL, is_answer TINYINT(1) NOT NULL, INDEX IDX_C1AB5A921E27F6BF (question_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE course (id INT AUTO_INCREMENT NOT NULL, teacher INT DEFAULT NULL, level INT DEFAULT NULL, subject INT DEFAULT NULL, name VARCHAR(255) NOT NULL, description LONGTEXT NOT NULL, is_valid TINYINT(1) DEFAULT NULL, content LONGTEXT NOT NULL, date_creation DATETIME NOT NULL, date_update DATETIME NOT NULL, INDEX IDX_169E6FB9B0F6A6D5 (teacher), INDEX IDX_169E6FB99AEACC13 (level), INDEX IDX_169E6FB9FBCE3E7A (subject), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE desktop_document (id INT AUTO_INCREMENT NOT NULL, student_id INT NOT NULL, name VARCHAR(255) NOT NULL, INDEX IDX_DCF040BCB944F1A (student_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE document (id INT AUTO_INCREMENT NOT NULL, course_id INT NOT NULL, name VARCHAR(150) NOT NULL, description VARCHAR(255) NOT NULL, INDEX IDX_D8698A76591CC992 (course_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE event (id INT AUTO_INCREMENT NOT NULL, student_id INT NOT NULL, title VARCHAR(255) NOT NULL, url LONGTEXT DEFAULT NULL, class VARCHAR(255) NOT NULL, content LONGTEXT DEFAULT NULL, start_date DATETIME NOT NULL, end_date DATETIME DEFAULT NULL, INDEX IDX_3BAE0AA7CB944F1A (student_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE exam (id INT AUTO_INCREMENT NOT NULL, course_id INT NOT NULL, content LONGTEXT NOT NULL, correction LONGTEXT NOT NULL, INDEX IDX_38BBA6C6591CC992 (course_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE exercise (id INT AUTO_INCREMENT NOT NULL, course_id INT NOT NULL, content LONGTEXT NOT NULL, correction LONGTEXT NOT NULL, INDEX IDX_AEDAD51C591CC992 (course_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE forum (id INT AUTO_INCREMENT NOT NULL, level INT NOT NULL, UNIQUE INDEX UNIQ_852BBECD9AEACC13 (level), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE historic_action (id INT AUTO_INCREMENT NOT NULL, actor_id INT NOT NULL, description VARCHAR(255) NOT NULL, date DATETIME NOT NULL, link VARCHAR(500) DEFAULT NULL, INDEX IDX_E43A43B810DAF24A (actor_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE kinship (id INT AUTO_INCREMENT NOT NULL, tutor_id INT NOT NULL, student_id INT NOT NULL, INDEX IDX_C65064E7208F64F1 (tutor_id), UNIQUE INDEX UNIQ_C65064E7CB944F1A (student_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE level (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(150) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE level_subject (level_id INT NOT NULL, subject_id INT NOT NULL, INDEX IDX_5A661B655FB14BA7 (level_id), INDEX IDX_5A661B6523EDC87 (subject_id), PRIMARY KEY(level_id, subject_id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE member (id INT AUTO_INCREMENT NOT NULL, email VARCHAR(180) NOT NULL, roles JSON NOT NULL, password VARCHAR(255) NOT NULL, first_name VARCHAR(255) NOT NULL, last_name VARCHAR(255) NOT NULL, is_active TINYINT(1) NOT NULL, date_registration DATETIME NOT NULL, date_birth DATE NOT NULL, UNIQUE INDEX UNIQ_70E4FA78E7927C74 (email), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE post (id INT AUTO_INCREMENT NOT NULL, author INT NOT NULL, topic INT NOT NULL, content LONGTEXT NOT NULL, date DATETIME NOT NULL, INDEX IDX_5A8A6C8DBDAFD8C8 (author), INDEX IDX_5A8A6C8D9D40DE1B (topic), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE question (id INT AUTO_INCREMENT NOT NULL, quiz_id INT NOT NULL, content VARCHAR(255) NOT NULL, INDEX IDX_B6F7494E853CD175 (quiz_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE quiz (id INT AUTO_INCREMENT NOT NULL, course_id INT NOT NULL, INDEX IDX_A412FA92591CC992 (course_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE quiz_attempt (id INT AUTO_INCREMENT NOT NULL, student_id INT NOT NULL, quiz_id INT NOT NULL, date DATETIME NOT NULL, score DOUBLE PRECISION NOT NULL, is_complete TINYINT(1) NOT NULL, INDEX IDX_AB6AFC6CB944F1A (student_id), INDEX IDX_AB6AFC6853CD175 (quiz_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE subject (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(150) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE subscription (id INT AUTO_INCREMENT NOT NULL, student INT NOT NULL, level INT NOT NULL, date_start DATETIME NOT NULL, date_end DATETIME NOT NULL, is_active TINYINT(1) NOT NULL, price DOUBLE PRECISION NOT NULL, INDEX IDX_A3C664D3B723AF33 (student), INDEX IDX_A3C664D39AEACC13 (level), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE subscription_subject (subscription_id INT NOT NULL, subject_id INT NOT NULL, INDEX IDX_88DBDEE19A1887DC (subscription_id), INDEX IDX_88DBDEE123EDC87 (subject_id), PRIMARY KEY(subscription_id, subject_id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE topic (id INT AUTO_INCREMENT NOT NULL, subject INT NOT NULL, author INT NOT NULL, forum INT NOT NULL, title VARCHAR(255) NOT NULL, content LONGTEXT NOT NULL, date DATETIME NOT NULL, INDEX IDX_9D40DE1BFBCE3E7A (subject), INDEX IDX_9D40DE1BBDAFD8C8 (author), INDEX IDX_9D40DE1B852BBECD (forum), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE video (id INT AUTO_INCREMENT NOT NULL, course_id INT NOT NULL, name VARCHAR(150) NOT NULL, description VARCHAR(255) NOT NULL, INDEX IDX_7CC7DA2C591CC992 (course_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE answer ADD CONSTRAINT FK_DADD4A25CB944F1A FOREIGN KEY (student_id) REFERENCES member (id)');
        $this->addSql('ALTER TABLE answer ADD CONSTRAINT FK_DADD4A251E27F6BF FOREIGN KEY (question_id) REFERENCES question (id)');
        $this->addSql('ALTER TABLE answer ADD CONSTRAINT FK_DADD4A25998666D1 FOREIGN KEY (choice_id) REFERENCES choice (id)');
        $this->addSql('ALTER TABLE answer ADD CONSTRAINT FK_DADD4A25F8FE9957 FOREIGN KEY (quiz_attempt_id) REFERENCES quiz_attempt (id)');
        $this->addSql('ALTER TABLE choice ADD CONSTRAINT FK_C1AB5A921E27F6BF FOREIGN KEY (question_id) REFERENCES question (id)');
        $this->addSql('ALTER TABLE course ADD CONSTRAINT FK_169E6FB9B0F6A6D5 FOREIGN KEY (teacher) REFERENCES member (id)');
        $this->addSql('ALTER TABLE course ADD CONSTRAINT FK_169E6FB99AEACC13 FOREIGN KEY (level) REFERENCES level (id)');
        $this->addSql('ALTER TABLE course ADD CONSTRAINT FK_169E6FB9FBCE3E7A FOREIGN KEY (subject) REFERENCES subject (id)');
        $this->addSql('ALTER TABLE desktop_document ADD CONSTRAINT FK_DCF040BCB944F1A FOREIGN KEY (student_id) REFERENCES member (id)');
        $this->addSql('ALTER TABLE document ADD CONSTRAINT FK_D8698A76591CC992 FOREIGN KEY (course_id) REFERENCES course (id)');
        $this->addSql('ALTER TABLE event ADD CONSTRAINT FK_3BAE0AA7CB944F1A FOREIGN KEY (student_id) REFERENCES member (id)');
        $this->addSql('ALTER TABLE exam ADD CONSTRAINT FK_38BBA6C6591CC992 FOREIGN KEY (course_id) REFERENCES course (id)');
        $this->addSql('ALTER TABLE exercise ADD CONSTRAINT FK_AEDAD51C591CC992 FOREIGN KEY (course_id) REFERENCES course (id)');
        $this->addSql('ALTER TABLE forum ADD CONSTRAINT FK_852BBECD9AEACC13 FOREIGN KEY (level) REFERENCES level (id)');
        $this->addSql('ALTER TABLE historic_action ADD CONSTRAINT FK_E43A43B810DAF24A FOREIGN KEY (actor_id) REFERENCES member (id)');
        $this->addSql('ALTER TABLE kinship ADD CONSTRAINT FK_C65064E7208F64F1 FOREIGN KEY (tutor_id) REFERENCES member (id)');
        $this->addSql('ALTER TABLE kinship ADD CONSTRAINT FK_C65064E7CB944F1A FOREIGN KEY (student_id) REFERENCES member (id)');
        $this->addSql('ALTER TABLE level_subject ADD CONSTRAINT FK_5A661B655FB14BA7 FOREIGN KEY (level_id) REFERENCES level (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE level_subject ADD CONSTRAINT FK_5A661B6523EDC87 FOREIGN KEY (subject_id) REFERENCES subject (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE post ADD CONSTRAINT FK_5A8A6C8DBDAFD8C8 FOREIGN KEY (author) REFERENCES member (id)');
        $this->addSql('ALTER TABLE post ADD CONSTRAINT FK_5A8A6C8D9D40DE1B FOREIGN KEY (topic) REFERENCES topic (id)');
        $this->addSql('ALTER TABLE question ADD CONSTRAINT FK_B6F7494E853CD175 FOREIGN KEY (quiz_id) REFERENCES quiz (id)');
        $this->addSql('ALTER TABLE quiz ADD CONSTRAINT FK_A412FA92591CC992 FOREIGN KEY (course_id) REFERENCES course (id)');
        $this->addSql('ALTER TABLE quiz_attempt ADD CONSTRAINT FK_AB6AFC6CB944F1A FOREIGN KEY (student_id) REFERENCES member (id)');
        $this->addSql('ALTER TABLE quiz_attempt ADD CONSTRAINT FK_AB6AFC6853CD175 FOREIGN KEY (quiz_id) REFERENCES quiz (id)');
        $this->addSql('ALTER TABLE subscription ADD CONSTRAINT FK_A3C664D3B723AF33 FOREIGN KEY (student) REFERENCES member (id)');
        $this->addSql('ALTER TABLE subscription ADD CONSTRAINT FK_A3C664D39AEACC13 FOREIGN KEY (level) REFERENCES level (id)');
        $this->addSql('ALTER TABLE subscription_subject ADD CONSTRAINT FK_88DBDEE19A1887DC FOREIGN KEY (subscription_id) REFERENCES subscription (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE subscription_subject ADD CONSTRAINT FK_88DBDEE123EDC87 FOREIGN KEY (subject_id) REFERENCES subject (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE topic ADD CONSTRAINT FK_9D40DE1BFBCE3E7A FOREIGN KEY (subject) REFERENCES subject (id)');
        $this->addSql('ALTER TABLE topic ADD CONSTRAINT FK_9D40DE1BBDAFD8C8 FOREIGN KEY (author) REFERENCES member (id)');
        $this->addSql('ALTER TABLE topic ADD CONSTRAINT FK_9D40DE1B852BBECD FOREIGN KEY (forum) REFERENCES forum (id)');
        $this->addSql('ALTER TABLE video ADD CONSTRAINT FK_7CC7DA2C591CC992 FOREIGN KEY (course_id) REFERENCES course (id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE answer DROP FOREIGN KEY FK_DADD4A25998666D1');
        $this->addSql('ALTER TABLE document DROP FOREIGN KEY FK_D8698A76591CC992');
        $this->addSql('ALTER TABLE exam DROP FOREIGN KEY FK_38BBA6C6591CC992');
        $this->addSql('ALTER TABLE exercise DROP FOREIGN KEY FK_AEDAD51C591CC992');
        $this->addSql('ALTER TABLE quiz DROP FOREIGN KEY FK_A412FA92591CC992');
        $this->addSql('ALTER TABLE video DROP FOREIGN KEY FK_7CC7DA2C591CC992');
        $this->addSql('ALTER TABLE topic DROP FOREIGN KEY FK_9D40DE1B852BBECD');
        $this->addSql('ALTER TABLE course DROP FOREIGN KEY FK_169E6FB99AEACC13');
        $this->addSql('ALTER TABLE forum DROP FOREIGN KEY FK_852BBECD9AEACC13');
        $this->addSql('ALTER TABLE level_subject DROP FOREIGN KEY FK_5A661B655FB14BA7');
        $this->addSql('ALTER TABLE subscription DROP FOREIGN KEY FK_A3C664D39AEACC13');
        $this->addSql('ALTER TABLE answer DROP FOREIGN KEY FK_DADD4A25CB944F1A');
        $this->addSql('ALTER TABLE course DROP FOREIGN KEY FK_169E6FB9B0F6A6D5');
        $this->addSql('ALTER TABLE desktop_document DROP FOREIGN KEY FK_DCF040BCB944F1A');
        $this->addSql('ALTER TABLE event DROP FOREIGN KEY FK_3BAE0AA7CB944F1A');
        $this->addSql('ALTER TABLE historic_action DROP FOREIGN KEY FK_E43A43B810DAF24A');
        $this->addSql('ALTER TABLE kinship DROP FOREIGN KEY FK_C65064E7208F64F1');
        $this->addSql('ALTER TABLE kinship DROP FOREIGN KEY FK_C65064E7CB944F1A');
        $this->addSql('ALTER TABLE post DROP FOREIGN KEY FK_5A8A6C8DBDAFD8C8');
        $this->addSql('ALTER TABLE quiz_attempt DROP FOREIGN KEY FK_AB6AFC6CB944F1A');
        $this->addSql('ALTER TABLE subscription DROP FOREIGN KEY FK_A3C664D3B723AF33');
        $this->addSql('ALTER TABLE topic DROP FOREIGN KEY FK_9D40DE1BBDAFD8C8');
        $this->addSql('ALTER TABLE answer DROP FOREIGN KEY FK_DADD4A251E27F6BF');
        $this->addSql('ALTER TABLE choice DROP FOREIGN KEY FK_C1AB5A921E27F6BF');
        $this->addSql('ALTER TABLE question DROP FOREIGN KEY FK_B6F7494E853CD175');
        $this->addSql('ALTER TABLE quiz_attempt DROP FOREIGN KEY FK_AB6AFC6853CD175');
        $this->addSql('ALTER TABLE answer DROP FOREIGN KEY FK_DADD4A25F8FE9957');
        $this->addSql('ALTER TABLE course DROP FOREIGN KEY FK_169E6FB9FBCE3E7A');
        $this->addSql('ALTER TABLE level_subject DROP FOREIGN KEY FK_5A661B6523EDC87');
        $this->addSql('ALTER TABLE subscription_subject DROP FOREIGN KEY FK_88DBDEE123EDC87');
        $this->addSql('ALTER TABLE topic DROP FOREIGN KEY FK_9D40DE1BFBCE3E7A');
        $this->addSql('ALTER TABLE subscription_subject DROP FOREIGN KEY FK_88DBDEE19A1887DC');
        $this->addSql('ALTER TABLE post DROP FOREIGN KEY FK_5A8A6C8D9D40DE1B');
        $this->addSql('DROP TABLE answer');
        $this->addSql('DROP TABLE choice');
        $this->addSql('DROP TABLE course');
        $this->addSql('DROP TABLE desktop_document');
        $this->addSql('DROP TABLE document');
        $this->addSql('DROP TABLE event');
        $this->addSql('DROP TABLE exam');
        $this->addSql('DROP TABLE exercise');
        $this->addSql('DROP TABLE forum');
        $this->addSql('DROP TABLE historic_action');
        $this->addSql('DROP TABLE kinship');
        $this->addSql('DROP TABLE level');
        $this->addSql('DROP TABLE level_subject');
        $this->addSql('DROP TABLE member');
        $this->addSql('DROP TABLE post');
        $this->addSql('DROP TABLE question');
        $this->addSql('DROP TABLE quiz');
        $this->addSql('DROP TABLE quiz_attempt');
        $this->addSql('DROP TABLE subject');
        $this->addSql('DROP TABLE subscription');
        $this->addSql('DROP TABLE subscription_subject');
        $this->addSql('DROP TABLE topic');
        $this->addSql('DROP TABLE video');
    }
}
