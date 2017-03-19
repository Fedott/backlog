<?php declare(strict_types=1);

#if (${NAMESPACE})
namespace ${NAMESPACE};
#end

#if (${NAMESPACE} && !${TESTED_NAMESPACE})
use ${TESTED_NAME};
#elseif (${TESTED_NAMESPACE} && ${NAMESPACE} != ${TESTED_NAMESPACE})
use ${TESTED_NAMESPACE}\\${TESTED_NAME};
#end
use PHPUnit\Framework\TestCase;

class ${NAME} extends TestCase {

}
