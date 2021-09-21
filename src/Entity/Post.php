<?php

namespace App\Entity;

use App\Attribute\ApiAuthGroups;
use Doctrine\ORM\Mapping as ORM;
use App\Controller\EmptyContoller;
use App\Repository\PostRepository;
use App\Controller\PostCountController;
use App\Controller\PostImageController;
use App\Controller\PostPublishController;
use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Annotation\ApiResource;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Validator\Constraints\Valid;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints\Length;
use Vich\UploaderBundle\Mapping\Annotation as Vich;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\SearchFilter;

/**
 * @ORM\Entity(repositoryClass=PostRepository::class)
 * @Vich\Uploadable()
 */
#[
    ApiResource(
        normalizationContext: [
            'groups' => ['read:collection'],
            'openapi_definition_name' => 'Collection'
        ],
        denormalizationContext: ['groups' => ['write:Post']],
        #paginationItemsPerPage: 2,
        #paginationClientItemsPerPage: true,
        graphql: [
            'item_query',
            'collection_query',
            'create' => [
                'validation_groups' => ['create:Post']
            ],
            'update',
        ],
        collectionOperations: [
            'get' => [
                'openapi_context' => [
                    #'security' => ['cookieAuth' => []]
                    'security' => [['bearerAuth' => []]]
                ]
            ],
            'post' => [
                #'validation_groups' => ['create:Post']
            ],
            'count' => [
                'method' => 'GET',
                'path' => '/posts/count',
                'controller' => PostCountController::class,
                'read' => false,
                'pagination_enabled' => false,
                'filters' => [],
                'openapi_context' => [
                    'summary' => 'Récupère le nombre total d\'article ',
                    'parameters' => [
                        [
                            'in' => 'query',
                            'name' => 'online',
                            'schema' => [
                                'type' => 'integer',
                                'maximum' => 1,
                                'minimum' => 0
                            ],
                            'description' => 'Filtre les article en ligne'
                        ]
                    ],
                    'responses' => [
                        '200' => [
                            'description' => 'Ok',
                            'content' => [
                                'application/json' => [
                                    'schema' => [
                                        'type' => 'integer',
                                        'example' => 3
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ],
        itemOperations: [
            'put',
            'delete',
            'get' => [
                'normalization_context' => [
                    'groups' => ['read:collection', 'read:item', 'read:Post'],
                    'openapi_definition_name' => 'Detail'
                ]
            ],
            'publish' => [
                'method' => 'POST',
                'path' => '/posts/{id}/publish',
                'controller' => PostPublishController::class,
                'openapi_context' => [
                    'summary' => 'Access à la publication d un article',
                    'requestBody' => [
                        'content' => [
                            'application/json' => [
                                'schema' => []
                            ]
                        ]
                    ]
                ]
            ],
            'image' => [
                'method' => 'POST',
                'path' => '/posts/{id}/image',
                #'deserialize' => false,
                'controller' => EmptyContoller::class,
                #'controller' => PostImageController::class,
                'openapi_context' => [
                    'requestBody' => [
                        'content' => [
                            'multipart/form-data' => [
                                'schema' => [
                                    'type' => 'object',
                                    'properties' => [
                                        'file' => [
                                            'type' => 'string',
                                            'format' => 'binary'
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ]
    ),
    ApiFilter(SearchFilter::class, properties: ['id' => 'exact', 'title' => 'partial']),
    ApiAuthGroups([
        'CAN_EDIT' => ['read:collection:Owner'],
        'ROLE_USER' => ['read:collection:User'],
    ])
]
class Post implements UserOwnedInterface
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    #[Groups(['read:collection'])]
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    #[
        Groups(['read:collection', 'write:Post']),
        Length(min: 5, groups: ['create:Post'])
    ]
    private $title;

    /**
     * @ORM\Column(type="string", length=255)
     */
    #[Groups(['read:collection:User', 'write:Post'])]
    private $slug;

    /**
     * @ORM\Column(type="text")
     */
    #[Groups(['read:item', 'write:Post'])]
    private $content;

    /**
     * @ORM\Column(type="datetime")
     */
    #[Groups(['read:item'])]
    private $createdAt;

    /**
     * @ORM\Column(type="datetime_immutable")
     */
    private $updatedAt;

    /**
     * @ORM\ManyToOne(targetEntity=Category::class, inversedBy="posts", cascade={"persist"})
     */
    #[
        Groups(['read:item', 'write:Post']),
        Valid()
    ]
    private $category;

    /**
     * @ORM\Column(type="boolean", options={"default": "0"})
     */
    #[Groups(['read:collection:Owner'])]
    private $online = false;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="posts", cascade={"persist"})
     */
    private $user;

    /**
     * @var File|null
     * @Vich\UploadableField(mapping="post_image", fileNameProperty="filePath")
     */
    #[Groups(['write:Post'])]
    private $file;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    #[Groups(['read:collection'])]
    private $filePath;

    /**
     * @var string|null
     */
    #[Groups(['read:collection'])]
    private $fileUrl;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): self
    {
        $this->slug = $slug;

        return $this;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(string $content): self
    {
        $this->content = $content;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTimeImmutable $updatedAt): self
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    public function getCategory(): ?Category
    {
        return $this->category;
    }

    public function setCategory(?Category $category): self
    {
        $this->category = $category;

        return $this;
    }

    public function getOnline(): ?bool
    {
        return $this->online;
    }

    public function setOnline(bool $online): self
    {
        $this->online = $online;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getFilePath(): ?string
    {
        return $this->filePath;
    }

    public function setFilePath(?string $filePath): self
    {
        $this->filePath = $filePath;

        return $this;
    }

    /**
     * @return  File|null
     */
    public function getFile(): ?File
    {
        return $this->file;
    }

    /**
     * @param  File|null  $file
     * @return  self
     */
    public function setFile(?File $file): Post
    {
        $this->file = $file;
        return $this;
    }

    /**
     * @return  string|null
     */
    public function getFileUrl(): ?string
    {
        return $this->fileUrl;
    }

    /**
     * @param  string|null  $fileUrl
     * @return  Post
     */
    public function setFileUrl(?string $fileUrl): Post
    {
        $this->fileUrl = $fileUrl;
        return $this;
    }
}
