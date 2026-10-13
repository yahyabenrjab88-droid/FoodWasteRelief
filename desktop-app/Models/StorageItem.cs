using System;

namespace FoodWasteRelief.Models;

public class StorageItem
{
    public int Id { get; set; }
    public int BusinessId { get; set; }
    public string ItemName { get; set; } = "";
    public decimal Quantity { get; set; }
    public string Unit { get; set; } = "";
    public DateTime? ExpiryDate { get; set; }
    public DateTime AddedAt { get; set; }

    public string ComboLabel => string.IsNullOrWhiteSpace(Unit)
        ? $"{ItemName} ({Quantity:0.##})"
        : $"{ItemName} ({Quantity:0.##} {Unit})";
}
