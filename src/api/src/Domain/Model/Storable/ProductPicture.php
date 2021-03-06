<?php

declare(strict_types=1);

namespace App\Domain\Model\Storable;

use Symfony\Component\Validator\Constraints as Assert;

use function strtolower;

final class ProductPicture extends Storable
{
    /** @Assert\Choice({"png", "jpg"}, message="product.pictures_extensions") */
    public function getExtension(): string
    {
        return strtolower($this->fileInfo->getExtension());
    }
}
