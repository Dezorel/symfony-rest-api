<?php
namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use JsonSerializable;
use ReturnTypeWillChange;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\AuthorRepository")
 * @ORM\Table(name="Authors")
 * @ORM\HasLifecycleCallbacks()
 */
class Author implements JsonSerializable {

    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\Column(type="datetime")
     */
    private ?\DateTime $create_date;

    /**
     * @ORM\Column(type="string", length=100)
     *
     */
    private string $name;

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }
    /**
     * @param int $id
     */
    public function setId(int $id)
    {
        $this->id = $id;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName(string $name)
    {
        $this->name = $name;
    }

    /**
     * @return \DateTime|null
     */
    public function getCreateDate(): ?\DateTime
    {
        return $this->create_date;
    }

    /**
     * @param \DateTime $create_date
     * @return Author
     */
    public function setCreateDate(\DateTime $create_date): self
    {
        $this->create_date = $create_date;
        return $this;
    }

    /**
     * @throws \Exception
     * @ORM\PrePersist()
     */
    public function beforeSave(){

        $this->create_date = new \DateTime('now');
    }

    /**
     * Specify data which should be serialized to JSON
     * @link https://php.net/manual/en/jsonserializable.jsonserialize.php
     * @return array data which can be serialized by <b>json_encode</b>,
     * which is a value of any type other than a resource.
     * @since 5.4.0
     */
    #[ReturnTypeWillChange] public function jsonSerialize(): array
    {
        return [
            "id" => $this->getId(),
            "name" => $this->getName(),
            "created_date" => $this->getCreateDate(),
        ];
    }
}