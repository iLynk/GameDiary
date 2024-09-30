<?php

namespace App\Form\Traits;

use DateTimeImmutable;
use Symfony\Component\Form\Event\PostSubmitEvent;

trait DateTrait
{
    public function dateTrait(): callable{
        return function(PostSubmitEvent $event): void{
            $data = $event->getData();
            $currentDate = new DateTimeImmutable();
            if(!$data->getId()){
                $data->setCreatedAt($currentDate);
            }
            $data->setUpdatedAt($currentDate);
        };
    }
}
