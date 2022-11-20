<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace app\classes\db;

trait SoftDeleteActiveRecordTrait
{
  public abstract function softDelete();
  public function softUndelete() { ; }

  protected function deleteInternal()
  {
    try
    {
      parent::deleteInternal(); //HARD DELETE
    } catch(\Throwable $th) {
      $this->softDelete();
    }
  }

  public function undelete()
  {
    $this->softUndelete();
  }

}
