<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%member}}`.
 */
class m221015_160302_create_member_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%member}}', [
            'mbr_usrId' => $this->bigInteger()->unsigned()->notNull()->unique(),
            'mbrSubscribeCode' => $this->integer()->unsigned()->unique(),
            'mbrStatus' => $this->string(1)->notNull()->defaultValue('A'),
        ]);

        $this->addForeignKey(
            'FK_tbl_member_tbl_user',
            '{{%member}}',
            'mbrId',
            '{{%user}}',
            'usrId',
            'CASCADE'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%member}}');
    }
}
