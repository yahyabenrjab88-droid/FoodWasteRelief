using System;

namespace FoodWasteRelief.Models;

public class Donation
{
    public int Id { get; set; }
    public int? ProduitId { get; set; }
    public int? StorageItemId { get; set; }
    public int AssociationId { get; set; }
    public int Quantite { get; set; }
    public DateTime DateDonation { get; set; }
    public string Status { get; set; } = "Pending";

    /// <summary>Libellé article (produit ou ligne de stock).</summary>
    public string ProduitNom { get; set; } = "";
    public string AssociationNom { get; set; } = "";
    public string BusinessNom { get; set; } = "";

    /// <summary>Per-row UI flags (not persisted).</summary>
    public bool ShowAdminAcceptReject { get; set; }
    public bool ShowDeleteDonation { get; set; }
    public bool ShowAssociationCollect { get; set; }
}

