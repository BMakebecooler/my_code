<?php
namespace common\models\cmsContent;

/**
 * @property \DateInterval $elapsedTime
 *
 */
class CmsContentElementPromo extends CmsContentElement
{
    /**
     * @return \DateInterval
     */
    public function getElapsedTime()
    {
        $current = new \DateTime();
        $publishedTo = (new \DateTime())->setTimestamp($this->published_to);
        return $publishedTo->diff($current);
    }
}