<?php
/**
 * Author: Fabio Fonseca <ff@fabiofonseca.com.br>
 * Based on Adianti Framework (LGPL) - Pablo Dall’Oglio
 */
trait TRecordTrait
{
    

    protected function saveAggregateByChildIds($join_class, $foreign_key_parent, $foreign_key_child, $parent_id, $array_chidren){
        if ($array_chidren)  {
              // Objetivo é apagar apenas os registros que não são mais associados
              $criteria = new TCriteria;
              $criteria->add(new TFilter($foreign_key_parent, '=', $parent_id));
              $criteria->add(new TFilter($foreign_key_child,'NOT IN', $array_chidren));
              $repository = new TRepository($join_class);
              $repository->delete($criteria);
              unset($repository);
              unset($criteria);
    
              // seleciona todos os registros que estão associados no banco
              $criteria2 = new TCriteria;
              $criteria2->add(new TFilter($foreign_key_parent, '=', $parent_id));
              $repository2 = new TRepository($join_class);
              $objects = $repository2->load($criteria2);
              $array_children_onDB = array();
              foreach($objects as $childDB){
                  $array_children_onDB[] = $childDB->$foreign_key_child;
              }
              unset($repository2);
              foreach ($array_chidren as $child_id)
              {
                  // só cria a associação para os que ainda não eram associados com a classe parent
                  if (!(in_array($child_id, $array_children_onDB))){
                      //TTransaction::open('processo_digital');
                      $object_join_class = new $join_class;
                      $object_join_class->$foreign_key_child = $child_id;
                      $object_join_class->$foreign_key_parent = $parent_id;
                      $object_join_class->store();
                      //TTransaction::close();
                  }
              }
          }
          unset($array_children_onDB);
      }
	


    public function loadCompositeTrait($composite_class, $foreign_key, $id = NULL, $order = NULL, $ownerPrimaryKey = null)
    {
        $pk = $ownerPrimaryKey ? $ownerPrimaryKey :$this->getPrimaryKey(); // discover the primary key name
        $id = $id ? $id : $this->$pk; // if the user has not passed the ID, take the object ID
        $criteria = TCriteria::create( [$foreign_key => $id ], ['order' => $order] );
        $repository = new TRepository($composite_class);
        return $repository->load($criteria);
    }

}