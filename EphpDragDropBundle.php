<?php

namespace Ephp\DragDropBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class EphpDragDropBundle extends Bundle {

    public function getParent() {
        return 'PunkAveFileUploaderBundle';
    }

}
