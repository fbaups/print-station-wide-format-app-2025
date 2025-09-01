<?php
declare(strict_types=1);

use App\Migrations\AppBaseMigration as BaseMigration;

class CreateMediaClips extends BaseMigration
{
    /**
     * Up Method.
     *
     * More information on this method is available here:
     * https://book.cakephp.org/migrations/4/en/migrations.html#the-up-method
     * @return void
     */
    public function up(): void
    {
        $this->table('media_clips')
            ->addColumn('created', 'datetime', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('modified', 'datetime', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('name', 'string', [
                'default' => null,
                'limit' => 255,
                'null' => true,
            ])
            ->addColumn('description', 'string', [
                'default' => null,
                'limit' => 1024,
                'null' => true,
            ])
            ->addColumn('rank', 'integer', [
                'default' => null,
                'limit' => 10,
                'null' => true,
            ])
            ->addColumn('artifact_link', 'integer', [
                'default' => null,
                'limit' => 10,
                'null' => true,
            ])
            ->addColumn('activation', 'datetime', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('expiration', 'datetime', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('auto_delete', 'boolean', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('trim_start', 'float', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('trim_end', 'float', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('duration', 'float', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('fitting', 'string', [
                'default' => null,
                'limit' => 16,
                'null' => true,
            ])
            ->addColumn('muted', 'boolean', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('loop', 'boolean', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('autoplay', 'boolean', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addIndex(
                $this->index('description')
                    ->setName('media_clips_description_index')
            )
            ->addIndex(
                $this->index('created')
                    ->setName('media_clips_created_index')
            )
            ->addIndex(
                $this->index('modified')
                    ->setName('media_clips_modified_index')
            )
            ->addIndex(
                $this->index('name')
                    ->setName('media_clips_name_index')
            )
            ->addIndex(
                $this->index('rank')
                    ->setName('media_clips_rank_index')
            )
            ->addIndex(
                $this->index('artifact_link')
                    ->setName('media_clips_artifact_link_index')
            )
            ->addIndex(
                $this->index('activation')
                    ->setName('media_clips_activation_index')
            )
            ->addIndex(
                $this->index('expiration')
                    ->setName('media_clips_expiration_index')
            )
            ->addIndex(
                $this->index('auto_delete')
                    ->setName('media_clips_auto_delete_index')
            )
            ->addIndex(
                $this->index('trim_start')
                    ->setName('media_clips_trim_start_index')
            )
            ->addIndex(
                $this->index('trim_end')
                    ->setName('media_clips_trim_end_index')
            )
            ->addIndex(
                $this->index('duration')
                    ->setName('media_clips_duration_index')
            )
            ->addIndex(
                $this->index('fitting')
                    ->setName('media_clips_fitting_index')
            )
            ->addIndex(
                $this->index('muted')
                    ->setName('media_clips_muted_index')
            )
            ->addIndex(
                $this->index('loop')
                    ->setName('media_clips_loop_index')
            )
            ->addIndex(
                $this->index('autoplay')
                    ->setName('media_clips_autoplay_index')
            )
            ->create();

        $this->table('artifact_metadata')
            ->addColumn('duration', 'float', [
                'after' => 'exif',
                'default' => null,
                'length' => null,
                'null' => true,
            ])
            ->addIndex(
                $this->index('duration')
                    ->setName('artifact_metadata_duration_index')
            )
            ->update();
    }

    /**
     * Down Method.
     *
     * More information on this method is available here:
     * https://book.cakephp.org/phinx/0/en/migrations.html#the-down-method
     * @return void
     */
    public function down(): void
    {

        $this->table('artifact_metadata')
            ->removeIndexByName('artifact_metadata_duration_index')
            ->update();

        $this->table('artifact_metadata')
            ->removeColumn('duration')
            ->update();

        $this->table('media_clips')->drop()->save();
    }
}
