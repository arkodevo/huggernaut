<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

// The FlowClusterSeeder JSON used "balanced" as the channel slug, but the channel
// attribute's middle value is "fluid". The "balanced" slug belongs to semantic-mode,
// so those 27 senses ended up with the wrong designation in channel_id.
// This migration corrects channel_id to point to the "fluid" (channel) designation.

return new class extends Migration
{
    public function up(): void
    {
        $fluidId = DB::table('designations as d')
            ->join('attributes as a', 'a.id', '=', 'd.attribute_id')
            ->where('a.slug', 'channel')
            ->where('d.slug', 'fluid')
            ->value('d.id');

        $balancedId = DB::table('designations')->where('slug', 'balanced')->value('id');

        if (! $fluidId || ! $balancedId) {
            return;
        }

        DB::table('word_senses')
            ->where('channel_id', $balancedId)
            ->update(['channel_id' => $fluidId]);
    }

    public function down(): void
    {
        $balancedId = DB::table('designations')->where('slug', 'balanced')->value('id');

        $fluidId = DB::table('designations as d')
            ->join('attributes as a', 'a.id', '=', 'd.attribute_id')
            ->where('a.slug', 'channel')
            ->where('d.slug', 'fluid')
            ->value('d.id');

        if (! $fluidId || ! $balancedId) {
            return;
        }

        DB::table('word_senses')
            ->where('channel_id', $fluidId)
            ->update(['channel_id' => $balancedId]);
    }
};
