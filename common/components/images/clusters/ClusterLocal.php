<?php
/**
 * Created by PhpStorm.
 * User: ubuntu5
 * Date: 19.09.17
 * Time: 13:54
 */

namespace common\components\images\clusters;

use Exception;
use skeeks\cms\components\storage\ClusterLocal as SXClusterLocal;
use skeeks\sx\File;

class ClusterLocal extends SXClusterLocal
{

    /**
     * @param string $clusterFileUniqSrc
     * @param File $tmpFile
     *
     * @return string $clusterFileSrc
     */
    public function update($clusterFileUniqSrc, $tmpFile)
    {

        $clusterFileSrc = $this->getRootSrc($clusterFileUniqSrc);

        try {

            $this->deleteTmpDir($clusterFileUniqSrc);
            $this->delete($clusterFileUniqSrc);

            $tmpFile->copy($clusterFileSrc);

        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }

        return $clusterFileSrc;
    }
}