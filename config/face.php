<?php

return [
    // Euclidean distance threshold for face descriptor matching.
    // Lower value = stricter matching. Default 0.5 (tweak as needed).
    'threshold' => env('FACE_MATCH_THRESHOLD', 0.5),
];
