using FoodWasteRelief.Models;

namespace FoodWasteRelief.Helpers;

public static class Session
{
    public static User? CurrentUser { get; set; }
    public static bool IsAdmin => CurrentUser?.Role == "admin";
    public static bool IsBusiness => CurrentUser?.Role == "business";
    public static bool IsClient => CurrentUser?.Role == "client";
    public static bool IsAssociation => CurrentUser?.Role == "association";

    public static int? BusinessId { get; set; }
    public static int? LinkedBusinessId
    {
        get => BusinessId;
        set => BusinessId = value;
    }

    public static int? AssociationId { get; set; }
    public static int? LinkedAssociationId
    {
        get => AssociationId;
        set => AssociationId = value;
    }

    public static void Clear()
    {
        CurrentUser = null;
        BusinessId = null;
        AssociationId = null;
    }
}

