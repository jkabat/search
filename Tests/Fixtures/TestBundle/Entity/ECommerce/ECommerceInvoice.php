<?php

namespace Rollerworks\RecordFilterBundle\Tests\Fixtures\TestBundle\Entity\ECommerce;

use \Rollerworks\RecordFilterBundle\Annotation as RecordFilter;

/**
 * ECommerce-Invoice
 *
 * @RecordFilter\Field("id", type="Number")
 * @RecordFilter\Field("label", type="Rollerworks\RecordFilterBundle\Tests\Fixtures\InvoiceType")
 */
class ECommerceInvoice
{
    private $id;

    public function __construct()
    {
    }
}