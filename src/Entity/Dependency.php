<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiProperty;
use ApiPlatform\Core\Annotation\ApiResource;
use Ramsey\Uuid\Nonstandard\Uuid;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

#[ApiResource(
    collectionOperations: ['get', 'post'],
    itemOperations: [
        'get',
        'delete',
        'put' => [
            'denormalization_context' => [
                'groups' => ['put:Dependency']
            ]
        ]
    ],
    paginationEnabled: false
)]
class Dependency
{

    #[ApiProperty(
        identifier: true
    )]
    private string $uuid;

    #[
        ApiProperty(
            description: 'Nom de la dépendance'
        ),
        Length(min: 2),
        NotBlank()
    ]
    private string $name;

    #[
        ApiProperty(
            description: 'Version de la dépendance',
            openapiContext: [
                'example' => "5.3.*"
            ]
        ),
        Length(min: 2),
        NotBlank(),
        Groups(['put:Dependency'])
    ]
    private string $version;

    public function __construct(
        string $name,
        string $version,
    ) {
        $this->uuid = Uuid::uuid5(Uuid::NAMESPACE_URL, $name)->toString();
        $this->name = $name;
        $this->version = $version;
    }

    /**
     * Get the value of uuid
     */
    public function getUuid()
    {
        return $this->uuid;
    }

    /**
     * Get the value of name
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Get the value of version
     */
    public function getVersion()
    {
        return $this->version;
    }

    /**
     * Set the value of version
     *
     * @return  self
     */
    public function setVersion($version)
    {
        $this->version = $version;

        return $this;
    }
}
