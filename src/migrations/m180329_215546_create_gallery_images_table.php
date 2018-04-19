<?php
namespace matvik\modelGallery\migrations;

use yii\db\Migration;

/**
 * Handles the creation of table `gallery_images`.
 */
class m180329_215546_create_gallery_images_table extends Migration
{

    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->createTable('{{%gallery_images}}', [
            'id' => $this->primaryKey()->unsigned(),
            'category' => $this->string(255)->notNull(),
            'item_id' => $this->integer()->unsigned()->notNull(),
            'priority' => $this->integer()->unsigned()->notNull(),
        ]);
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->dropTable('{{%gallery_images}}');
    }
}
