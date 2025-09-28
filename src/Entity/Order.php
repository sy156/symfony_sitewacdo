<?php

namespace App\Entity;

use App\Repository\OrderRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: OrderRepository::class)]
#[ORM\Table(name: '`orders`')]
class Order
{
    // Constantes pour les statuts
    public const STATUT_EN_COURS = 'en_cours';
    public const STATUT_EN_PREPARATION = 'en_preparation';
    public const STATUT_LIVREE = 'livree';
    public const STATUT_ANNULEE = 'annulee';


    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'orders')]
    #[ORM\JoinColumn(nullable: true)]
    private ?User $user = null;

    #[ORM\Column(type: 'decimal', precision: 8, scale: 2)]
    private ?string $total = null;

    #[ORM\Column(type: 'string', length: 50)]
    private string $statut = self::STATUT_EN_COURS;

    #[ORM\Column(type: 'datetime_immutable')]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\OneToMany(mappedBy: 'order', targetEntity: OrderItem::class, cascade: ['remove'])]
    private Collection $orderItems;

    public function __construct()
    {
        $this->orderItems = new ArrayCollection();
        $this->createdAt = new \DateTimeImmutable();
        $this->statut = self::STATUT_EN_COURS;
    }

    /**
     * Alias pour Twig
     * @return Collection<int, OrderItem>
     */
    public function getItems(): Collection
    {
        return $this->orderItems;
    }
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;
        return $this;
    }

    public function getTotal(): ?string
    {
        return $this->total;
    }

    public function setTotal(string $total): static
    {
        $this->total = $total;
        return $this;
    }

    public function getStatut(): string
    {
        return $this->statut;
    }

    public function setStatut(string $statut): static
    {
        if (!in_array($statut, [
            self::STATUT_EN_COURS,
            self::STATUT_EN_PREPARATION,
            self::STATUT_LIVREE,
            self::STATUT_ANNULEE
        ])) {
            throw new \InvalidArgumentException('Statut invalide');
        }

        $this->statut = $statut;
        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    /**
     * @return Collection<int, OrderItem>
     */
    public function getOrderItems(): Collection
    {
        return $this->orderItems;
    }

    public function addOrderItem(OrderItem $orderItem): static
    {
        if (!$this->orderItems->contains($orderItem)) {
            $this->orderItems->add($orderItem);
            $orderItem->setOrder($this);
        }
        return $this;
    }

    public function removeOrderItem(OrderItem $orderItem): static
    {
        if ($this->orderItems->removeElement($orderItem)) {
            if ($orderItem->getOrder() === $this) {
                $orderItem->setOrder(null);
            }
        }
        return $this;
    }

    // Méthodes utiles pour vérifier le statut
    public function isEnCours(): bool
    {
        return $this->statut === self::STATUT_EN_COURS;
    }

    public function isEnPreparation(): bool
    {
        return $this->statut === self::STATUT_EN_PREPARATION;
    }

    public function isLivree(): bool
    {
        return $this->statut === self::STATUT_LIVREE;
    }

    public function isAnnulee(): bool
    {
        return $this->statut === self::STATUT_ANNULEE;
    }

    // Méthodes pour changer le statut
    public function marquerEnPreparation(): void
    {
        if (!$this->isEnCours()) {
            throw new \LogicException('Seules les commandes en cours peuvent passer en préparation');
        }
        $this->statut = self::STATUT_EN_PREPARATION;
    }

    public function marquerLivree(): void
    {
        if (!$this->isEnPreparation()) {
            throw new \LogicException('Seules les commandes en préparation peuvent être livrées');
        }
        $this->statut = self::STATUT_LIVREE;
    }

    public function annuler(): void
    {
        if ($this->isLivree()) {
            throw new \LogicException('Impossible d\'annuler une commande déjà livrée');
        }
        $this->statut = self::STATUT_ANNULEE;
    }

    // Méthode pour obtenir le libellé du statut
    public function getStatutLabel(): string
    {
        return match ($this->statut) {
            self::STATUT_EN_COURS => 'En cours',
            self::STATUT_EN_PREPARATION => 'En préparation',
            self::STATUT_LIVREE => 'Livrée',
            self::STATUT_ANNULEE => 'Annulée',
            default => 'Inconnu'
        };
    }

    // Méthode pour obtenir la classe CSS du statut (pour l'affichage)
    public function getStatutClass(): string
    {
        return match ($this->statut) {
            self::STATUT_EN_COURS => 'bg-warning text-dark',
            self::STATUT_EN_PREPARATION => 'bg-info text-dark',
            self::STATUT_LIVREE => 'bg-success text-white',
            self::STATUT_ANNULEE => 'bg-danger text-white',
            default => 'bg-secondary text-white',
        };
    }
}
