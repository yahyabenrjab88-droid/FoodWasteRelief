using System;
using System.Collections.Generic;

namespace FoodWasteRelief.Models;

public class Commande
{
    public int Id { get; set; }
    public int ClientId { get; set; }
    public int BusinessId { get; set; }
    public DateTime DateCommande { get; set; }
    public decimal Total { get; set; }
    public string Status { get; set; } = "en_attente";

    public string ClientName { get; set; } = "";
    public string BusinessName { get; set; } = "";

    public List<CommandeDetail> Details { get; set; } = new();

    /// <summary>French label for grids (not persisted).</summary>
    public string StatusLabelFr { get; set; } = "";

    public bool CanConfirmOrder { get; set; }
    public bool CanMarkDelivered { get; set; }
    public bool CanCancelOrder { get; set; }
}

