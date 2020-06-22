<?php

/**
 * @var $context \common\widgets\content\StarsRating
 */
$context = $this->context;
$rating = $context->getValue();

?>


<div class="rating-container rating-custom-size rating-animate">
    <div class="rating">

        <span class="empty-stars">
            <span class="star"><i class="star-empty"></i></span>
            <span class="star"><i class="star-empty"></i></span>
            <span class="star"><i class="star-empty"></i></span>
            <span class="star"><i class="star-empty"></i></span>
            <span class="star"><i class="star-empty"></i></span>
        </span>

        <span class="filled-stars" style="width: <?= $context->getPercent() ?>%;">
                <span class="star"><i class="star-fill"></i></span>
                <span class="star"><i class="star-fill"></i></span>
                <span class="star"><i class="star-fill"></i></span>
                <span class="star"><i class="star-fill"></i></span>
                <span class="star"><i class="star-fill"></i></span>
        </span>

    </div>
</div>