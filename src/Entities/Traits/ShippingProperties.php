<?php

namespace Railroad\Usora\Entities\Traits;

trait ShippingProperties
{
    /**
     * @ORM\Column(type="boolean", name="drumeo_ship_magazine", nullable=false)
     * @var bool
     */
    protected $drumeoShipMagazine = false;

    /**
     * @ORM\Column(type="integer", name="magazine_shipping_address_id", nullable=true)
     * @var integer|null
     */
    protected $magazineShippingAddressId;

    /**
     * @return bool
     */
    public function getDrumeoShipMagazine(): bool
    {
        return $this->drumeoShipMagazine ?? false;
    }

    /**
     * @param bool $drumeoShipMagazine
     */
    public function setDrumeoShipMagazine(bool $drumeoShipMagazine): void
    {
        $this->drumeoShipMagazine = $drumeoShipMagazine;
    }

    /**
     * @return int|null
     */
    public function getMagazineShippingAddressId(): ?int
    {
        return $this->magazineShippingAddressId;
    }

    /**
     * @param int|null $magazineShippingAddressId
     */
    public function setMagazineShippingAddressId(?int $magazineShippingAddressId): void
    {
        $this->magazineShippingAddressId = $magazineShippingAddressId;
    }
}