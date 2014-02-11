<?php
/**
 * Created by PhpStorm.
 * User: byorty
 * Date: 31.12.13
 * Time: 21:18
 */

class LinkedDaoWorker extends PowerfullDaoWorker {

    protected function cacheByQuery(
        SelectQuery $query,
        /* Identifiable */ $object,
        $expires = Cache::DO_NOT_CACHE
    )
    {
        if ($expires !== Cache::DO_NOT_CACHE) {

            if (self::SUFFIX_ITEM == $this->getSuffixQuery()) {

                $idKey = $this->makeIdKey($object->getId());
                $queryKey = $this->makeQueryKey($query, $this->getSuffixQuery());

                if ($idKey != $queryKey) {
                    Cache::me()
                        ->mark($this->className)
                        ->add(
                            $queryKey,
                            CacheLink::create()
                                ->setKey($idKey),
                            $expires
                        );
                }

                Cache::me()
                    ->mark($this->className)
                    ->add(
                        $idKey,
                        $object,
                        $expires
                    );
            } else if (self::SUFFIX_LIST == $this->getSuffixQuery()) {
                /** @var CacheListLink $link */
                $link = CacheListLink::create();
                foreach ($object as $item) {
                    $idKey = $this->makeIdKey($item->getId());

                    Cache::me()
                        ->mark($this->className)
                        ->add(
                            $idKey,
                            $item,
                            $expires
                        );

                    $link->setKey($item->getId(), $idKey);
                }

                parent::cacheByQuery($query, $link, $expires);
            } else {
                parent::cacheByQuery($query, $object, $expires);
            }
        }

        return $object;
    }

    protected function getCachedByQuery(SelectQuery $query)
    {
        $object = Cache::me()
            ->mark($this->className)
            ->get(
                $this->makeQueryKey(
                    $query,
                    $this->getSuffixQuery()
                )
            )
        ;

        if ($object instanceof CacheLink) {
            $object = Cache::me()->get($object->getKey());
        } else if ($object instanceof CacheListLink) {

            $keys = $object->getKeys();
            $object = Cache::me()->getList($keys);

			foreach ($keys as $id => $key) {
                if (!$object[$key]) {
                    try {
                        $item = $this->dao->getById($id);
                        $object[$key] = $item;
                        Cache::me()
                            ->mark($this->className)
                            ->add(
                                $this->makeIdKey($id),
                                $item,
                                Cache::EXPIRES_MEDIUM
                            );
                    } catch (ObjectNotFoundException $e) {
                        unset($object[$key]);
                    }
                }
            }

			$object = array_values($object);
        }

        return $object;
    }
} 