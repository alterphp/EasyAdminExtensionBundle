<?php

namespace AlterPHP\EasyAdminExtensionBundle\Model;

use Doctrine\ORM\QueryBuilder;

abstract class CustomListFilter extends ListFilter
{
    public abstract function filter(QueryBuilder $queryBuilder): void;
}
