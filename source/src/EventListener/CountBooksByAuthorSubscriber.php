<?php

declare(strict_types=1);

namespace App\EventListener;

use App\Entity\Book;
use Doctrine\Common\EventSubscriber;
use Doctrine\Common\Persistence\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Events;

class CountBooksByAuthorSubscriber implements EventSubscriber
{
    public function getSubscribedEvents(): array
    {
        return [
            Events::postPersist,
            Events::preUpdate,
            Events::postUpdate,
            Events::postRemove,
        ];
    }

    public function postPersist(LifecycleEventArgs $event): void
    {
        $this->updateCount($event);
    }

    public function preUpdate(PreUpdateEventArgs $event): void
    {
        $entity = $event->getObject();

        if (!$entity instanceof Book) {
            return;
        }

        if (!$event->hasChangedField('author')) {
            return;
        }

        $entityManager = $event->getObjectManager();
        $author = $event->getOldValue('author');
        $count = $entityManager->getRepository(Book::class)->count(['author' => $author]);
        $author->setCountBooks(--$count);
        $entityManager->persist($author);
    }

    public function postUpdate(LifecycleEventArgs $event): void
    {
        $this->updateCount($event);
    }

    public function postRemove(LifecycleEventArgs $event): void
    {
        $this->updateCount($event);
    }

    private function updateCount(LifecycleEventArgs $event): void
    {
        $entity = $event->getObject();

        if (!$entity instanceof Book) {
            return;
        }

        $author = $entity->getAuthor();

        if (null === $author) {
            return;
        }

        $entityManager = $event->getObjectManager();
        $count = $entityManager->getRepository(Book::class)->count(['author' => $author]);
        $author->setCountBooks($count);
        $entityManager->persist($author);
        $entityManager->flush();
    }
}
