<?php

declare(strict_types=1);

namespace App\Domain\Book;

use App\Domain\Book\Event\BookDescriptionWasChanged;
use App\Domain\Book\Event\BookNameWasChanged;
use App\Domain\Book\Event\BookWasCreated;
use App\Domain\Book\Event\BookWasDeleted;
use App\Domain\Book\ValueObject\Description;
use App\Domain\Common\ValueObject\AggregateRootId;
use App\Domain\Common\ValueObject\Name;
use Prooph\EventSourcing\AggregateChanged;
use Prooph\EventSourcing\AggregateRoot;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

class Book extends AggregateRoot
{
    /**
     * @var AggregateRootId
     */
    private $id;

    /**
     * @var Name
     */
    private $name;

    /**
     * @var Description
     */
    private $description;

    /**
     * @var UuidInterface
     */
    private $category;

    /**
     * @var UuidInterface
     */
    private $author;

    public static function create(
        AggregateRootId $id,
        Name $name,
        ValueObject\Description $description,
        string $category,
        string $author
    ): self {
        $self = new self();
        $self->recordThat(BookWasCreated::createWithData($id, $name, $description, Uuid::fromString($category), Uuid::fromString($author)));

        return $self;
    }

    public function applyBookWasCreated(BookWasCreated $bookWasCreated)
    {
        $this->id = $bookWasCreated->getId();
        $this->name = $bookWasCreated->getName();
        $this->description = $bookWasCreated->getDescription();
        $this->author = $bookWasCreated->getAuthor();
        $this->category = $bookWasCreated->getCategory();
    }

    public function changeName(string $name)
    {
        $this->recordThat(BookNameWasChanged::createWithData($this->id, $this->name->changeName($name)));
    }

    public function changeDescription(string $description)
    {
        $this->description->changeDescription($description);
        $this->recordThat(BookDescriptionWasChanged::createWithData($this->id, $this->description));
    }

    public function delete()
    {
        $this->recordThat(BookWasDeleted::createWithData($this->id));
    }

    protected function applyBookWasDeleted(BookWasDeleted $bookWasDeleted)
    {
    }

    protected function applyBookDescriptionWasChanged(BookDescriptionWasChanged $bookDescriptionWasChanged)
    {
        $this->description = $bookDescriptionWasChanged->getDescription();
    }

    protected function applyBookNameWasChanged(BookNameWasChanged $bookNameWasChanged)
    {
        $this->name = $bookNameWasChanged->getName();
    }

    protected function aggregateId(): string
    {
        return $this->id->toString();
    }

    /**
     * Apply given event.
     */
    protected function apply(AggregateChanged $e): void
    {
        $handler = $this->determineEventHandlerMethodFor($e);
        if (!\method_exists($this, $handler)) {
            throw new \RuntimeException(\sprintf(
                'Missing event handler method %s for aggregate root %s',
                $handler,
                \get_class($this)
            ));
        }
        $this->{$handler}($e);
    }

    protected function determineEventHandlerMethodFor(AggregateChanged $e): string
    {
        return 'apply'.\implode(\array_slice(\explode('\\', \get_class($e)), -1));
    }
}
