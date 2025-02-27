<?php

namespace App\Service;

use Psr\Log\LoggerInterface;
use Doctrine\ORM\EntityManagerInterface;
use App\Service\ListManager;
use App\Entity\Todolist;
use App\Entity\Item;

use App\Objects\ItemStatus;

class ItemManager
{
    private LoggerInterface $logger;
    private EntityManagerInterface $entityManager;

    public function __construct(
        LoggerInterface $logger,
        EntityManagerInterface $entityManager)
    {
        $this->logger = $logger;
        $this->entityManager = $entityManager;
        $this->itemRepository = $this->entityManager->getRepository(Item::class);
        $this->todoListRepository = $this->entityManager->getRepository(Todolist::class);
    }

    public function create($parameters): Item
    {
        $this->logger->info("Creating new item: " . $parameters['item_name']);
        $todoList = $this->todoListRepository->find($parameters['list_id']);
        
        if (!$todoList) {
            throw new NotFoundHttpException("List not found");
        }
        
        $item = new Item();
        
        $item->setTodoList($todoList);
        $item->setName($parameters['item_name']);
        $item->setDescription($parameters['item_description']);

        $this->entityManager->persist($item);
        $this->entityManager->flush();

        $this->logger->info("Item '" . $item->getName() . "' was created");

        return $item;
    }

    public function changeStatus(int $id, ItemStatus $newStatus): Item
    {
        $item = $this->itemRepository->find($id);

        if (!$item) {
            throw new NotFoundHttpException("Item not found");
        }

        $this->logger->info("Changing item '" . $item->getName() . "' status to '" . $newStatus->name . "' ");

        $item->setStatus($newStatus);
        $this->entityManager->persist($item);
        $this->entityManager->flush();

        return $item;
    }
}