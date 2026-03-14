<?php

/*
|--------------------------------------------------------------------------
| Points Action Registry
|--------------------------------------------------------------------------
|
| 'earn'  — points awarded per action type.
| 'spend' — points deducted per spend action.
|
| These values are intentionally conservative for launch; adjust as data
| informs the right balance between accessibility and engagement.
|
*/

return [

    'earn' => [
        'daily_login'           => 2,
        'word_saved'            => 5,
        'flashcard_session'     => 10,
        'scenario_completed'    => 15,
        'streak_7day'           => 25,
        'streak_30day'          => 100,
        'first_word_view'       => 1,
        'example_contributed'   => 20,   // Phase 4
    ],

    'spend' => [
        'ai_workshop_generation' => 10,
        'ai_workshop_feedback'   => 5,
        'points_to_ai_credit'    => 1,   // 1 point = 1 AI credit on conversion (adjust post-launch)
    ],

];
