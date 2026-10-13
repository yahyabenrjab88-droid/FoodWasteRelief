using System;
using FoodWasteRelief.Models;

namespace FoodWasteRelief.Helpers;

/// <summary>Canonical order status values (DB) and French UI labels.</summary>
public static class OrderStatusHelper
{
    public const string EnAttente = "en_attente";
    public const string Confirmee = "confirmee";
    public const string Livree = "livree";
    public const string Annulee = "annulee";

    public static string Normalize(string? raw)
    {
        var s = (raw ?? "").Trim().ToLowerInvariant();
        if (s.Length == 0)
            return EnAttente;
        return s switch
        {
            "en attente" or "pending" => EnAttente,
            "confirmée" or "confirmee" or "confirmé" or "confirme" => Confirmee,
            "livrée" or "livree" or "livré" or "livre" => Livree,
            "annulée" or "annulee" or "annulé" or "annule" or "cancelled" or "canceled" => Annulee,
            _ => s
        };
    }

    public static string ToFrenchLabel(string? raw)
    {
        return Normalize(raw) switch
        {
            EnAttente => "En attente",
            Confirmee => "Confirmée",
            Livree => "Livrée",
            Annulee => "Annulée",
            _ => string.IsNullOrWhiteSpace(raw) ? "—" : raw!.Trim()
        };
    }

    public static bool IsTerminal(string? raw)
    {
        var n = Normalize(raw);
        return n == Livree || n == Annulee;
    }

    /// <summary>Returns false with <paramref name="message"/> when the transition is not allowed.</summary>
    public static bool CanTransition(string? currentRaw, string targetRaw, out string? message)
    {
        message = null;
        var cur = Normalize(currentRaw);
        var tgt = Normalize(targetRaw);

        if (cur == tgt)
        {
            message = "Le statut est déjà à jour.";
            return false;
        }

        if (IsTerminal(cur))
        {
            message = "Cette commande ne peut plus être modifiée.";
            return false;
        }

        if (tgt == Confirmee && cur == EnAttente)
            return true;
        if (tgt == Livree && cur == Confirmee)
            return true;
        if (tgt == Annulee && (cur == EnAttente || cur == Confirmee))
            return true;

        message = "Transition de statut non autorisée.";
        return false;
    }

    public static void ApplyCommandeUi(Commande o, bool isClient, bool isAdmin, bool isBusiness, int? linkedBusinessId)
    {
        o.StatusLabelFr = ToFrenchLabel(o.Status);
        if (isClient)
        {
            o.CanConfirmOrder = false;
            o.CanMarkDelivered = false;
            o.CanCancelOrder = false;
            return;
        }

        var canManage = isAdmin || (isBusiness && linkedBusinessId.HasValue && o.BusinessId == linkedBusinessId.Value);
        var n = Normalize(o.Status);
        o.CanConfirmOrder = canManage && n == EnAttente;
        o.CanMarkDelivered = canManage && n == Confirmee;
        o.CanCancelOrder = canManage && (n == EnAttente || n == Confirmee);
    }
}
