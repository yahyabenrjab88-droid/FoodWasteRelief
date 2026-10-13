using System;
using System.Collections.Generic;
using System.Linq;
using System.Threading.Tasks;
using FoodWasteRelief.Helpers;
using FoodWasteRelief.Models;
using MySql.Data.MySqlClient;

namespace FoodWasteRelief.Services;

public class OrderService
{
    public async Task<List<Commande>> GetAllAsync()
    {
        var list = new List<Commande>();
        await using var conn = await DatabaseService.GetConnectionAsync();
        const string sql = @"SELECT c.id, c.client_id, c.business_id, c.date_commande, c.total, c.status,
                                    CONCAT(u.prenom, ' ', u.nom) AS client_name,
                                    b.nom_business
                             FROM commandes c
                             JOIN users u ON u.id = c.client_id
                             JOIN business b ON b.id = c.business_id
                             ORDER BY c.id DESC";
        await using var cmd = new MySqlCommand(sql, conn);
        await using var reader = await cmd.ExecuteReaderAsync();
        while (await reader.ReadAsync())
        {
            list.Add(new Commande
            {
                Id = Convert.ToInt32(reader["id"]),
                ClientId = Convert.ToInt32(reader["client_id"]),
                BusinessId = Convert.ToInt32(reader["business_id"]),
                DateCommande = Convert.ToDateTime(reader["date_commande"]),
                Total = Convert.ToDecimal(reader["total"]),
                Status = Convert.ToString(reader["status"]) ?? "en_attente",
                ClientName = Convert.ToString(reader["client_name"]) ?? "",
                BusinessName = Convert.ToString(reader["nom_business"]) ?? ""
            });
        }

        return list;
    }

    public async Task<List<Commande>> GetByClientAsync(int clientId)
    {
        var list = new List<Commande>();
        await using var conn = await DatabaseService.GetConnectionAsync();
        const string sql = @"SELECT c.id, c.client_id, c.business_id, c.date_commande, c.total, c.status,
                                    CONCAT(u.prenom, ' ', u.nom) AS client_name,
                                    b.nom_business
                             FROM commandes c
                             JOIN users u ON u.id = c.client_id
                             JOIN business b ON b.id = c.business_id
                             WHERE c.client_id = @cid
                             ORDER BY c.id DESC";
        await using var cmd = new MySqlCommand(sql, conn);
        cmd.Parameters.AddWithValue("@cid", clientId);
        await using var reader = await cmd.ExecuteReaderAsync();
        while (await reader.ReadAsync())
        {
            list.Add(new Commande
            {
                Id = Convert.ToInt32(reader["id"]),
                ClientId = Convert.ToInt32(reader["client_id"]),
                BusinessId = Convert.ToInt32(reader["business_id"]),
                DateCommande = Convert.ToDateTime(reader["date_commande"]),
                Total = Convert.ToDecimal(reader["total"]),
                Status = Convert.ToString(reader["status"]) ?? "en_attente",
                ClientName = Convert.ToString(reader["client_name"]) ?? "",
                BusinessName = Convert.ToString(reader["nom_business"]) ?? ""
            });
        }

        return list;
    }

    /// <summary>Creates one order per business, inserts line items, decrements stock, clears the cart.</summary>
    public async Task<int> CheckoutCartAsync(int clientId)
    {
        await using var conn = await DatabaseService.GetConnectionAsync();
        await using var tx = (MySqlTransaction)await conn.BeginTransactionAsync();
        try
        {
            const string loadSql = @"SELECT ci.id AS cart_id, ci.product_id, ci.quantity AS cart_qty,
                                              p.business_id, p.prix, p.quantite AS stock, p.nom
                                       FROM cart_items ci
                                       INNER JOIN produits p ON p.id = ci.product_id AND p.is_active = 1
                                       WHERE ci.client_id = @cid";
            var rows = new List<(int CartId, int ProductId, int Qty, int BusinessId, decimal Prix, int Stock, string Nom)>();
            await using (var cmd = new MySqlCommand(loadSql, conn, tx))
            {
                cmd.Parameters.AddWithValue("@cid", clientId);
                await using var r = await cmd.ExecuteReaderAsync();
                while (await r.ReadAsync())
                {
                    rows.Add((
                        Convert.ToInt32(r["cart_id"]),
                        Convert.ToInt32(r["product_id"]),
                        Convert.ToInt32(r["cart_qty"]),
                        Convert.ToInt32(r["business_id"]),
                        Convert.ToDecimal(r["prix"]),
                        Convert.ToInt32(r["stock"]),
                        Convert.ToString(r["nom"]) ?? ""));
                }
            }

            if (rows.Count == 0)
                throw new Exception("Votre panier est vide.");

            foreach (var row in rows)
            {
                if (row.Qty > row.Stock)
                    throw new Exception($"Stock insuffisant pour « {row.Nom} ».");
            }

            var groups = rows.GroupBy(x => x.BusinessId).ToList();
            var createdOrders = 0;

            foreach (var g in groups)
            {
                var lines = g.ToList();
                var total = lines.Sum(x => x.Prix * x.Qty);

                await using var ins = new MySqlCommand(
                    "INSERT INTO commandes(client_id, business_id, date_commande, total, status) VALUES(@cl,@bid,NOW(),@tot,'en_attente')",
                    conn, tx);
                ins.Parameters.AddWithValue("@cl", clientId);
                ins.Parameters.AddWithValue("@bid", g.Key);
                ins.Parameters.AddWithValue("@tot", total);
                await ins.ExecuteNonQueryAsync();
                var commandeId = (int)ins.LastInsertedId;

                foreach (var line in lines)
                {
                    await using (var det = new MySqlCommand(
                                     @"INSERT INTO commandes_details(commande_id, produit_id, quantite, prix_unitaire)
                                       VALUES(@cmd, @pid, @qty, @pu)",
                                     conn, tx))
                    {
                        det.Parameters.AddWithValue("@cmd", commandeId);
                        det.Parameters.AddWithValue("@pid", line.ProductId);
                        det.Parameters.AddWithValue("@qty", line.Qty);
                        det.Parameters.AddWithValue("@pu", line.Prix);
                        await det.ExecuteNonQueryAsync();
                    }

                    await using (var upd = new MySqlCommand(
                                     @"UPDATE produits SET quantite = quantite - @q
                                       WHERE id = @pid AND quantite >= @q",
                                     conn, tx))
                    {
                        upd.Parameters.AddWithValue("@q", line.Qty);
                        upd.Parameters.AddWithValue("@pid", line.ProductId);
                        var n = await upd.ExecuteNonQueryAsync();
                        if (n <= 0)
                            throw new Exception($"Impossible de finaliser le stock pour « {line.Nom} ».");
                    }

                    await using var ia = new MySqlCommand(
                        "UPDATE produits SET is_active = CASE WHEN quantite <= 0 THEN 0 ELSE 1 END WHERE id=@pid",
                        conn, tx);
                    ia.Parameters.AddWithValue("@pid", line.ProductId);
                    await ia.ExecuteNonQueryAsync();
                }

                createdOrders++;
            }

            await using (var delC = new MySqlCommand("DELETE FROM cart_items WHERE client_id=@cid", conn, tx))
            {
                delC.Parameters.AddWithValue("@cid", clientId);
                await delC.ExecuteNonQueryAsync();
            }

            await tx.CommitAsync();
            return createdOrders;
        }
        catch
        {
            await tx.RollbackAsync();
            throw;
        }
    }

    public async Task<List<Commande>> GetByBusinessAsync(int businessId)
    {
        var list = new List<Commande>();
        await using var conn = await DatabaseService.GetConnectionAsync();
        const string sql = @"SELECT c.id, c.client_id, c.business_id, c.date_commande, c.total, c.status,
                                    CONCAT(u.prenom, ' ', u.nom) AS client_name,
                                    b.nom_business
                             FROM commandes c
                             JOIN users u ON u.id = c.client_id
                             JOIN business b ON b.id = c.business_id
                             WHERE c.business_id = @bid
                             ORDER BY c.id DESC";
        await using var cmd = new MySqlCommand(sql, conn);
        cmd.Parameters.AddWithValue("@bid", businessId);
        await using var reader = await cmd.ExecuteReaderAsync();
        while (await reader.ReadAsync())
        {
            list.Add(new Commande
            {
                Id = Convert.ToInt32(reader["id"]),
                ClientId = Convert.ToInt32(reader["client_id"]),
                BusinessId = Convert.ToInt32(reader["business_id"]),
                DateCommande = Convert.ToDateTime(reader["date_commande"]),
                Total = Convert.ToDecimal(reader["total"]),
                Status = Convert.ToString(reader["status"]) ?? OrderStatusHelper.EnAttente,
                ClientName = Convert.ToString(reader["client_name"]) ?? "",
                BusinessName = Convert.ToString(reader["nom_business"]) ?? ""
            });
        }

        return list;
    }

    /// <summary>
    /// Business: only its <paramref name="actingBusinessId"/>; admin: any order.
    /// <paramref name="requestedTargetStatus"/> must be <see cref="OrderStatusHelper.Confirmee"/>, <see cref="OrderStatusHelper.Livree"/>, or <see cref="OrderStatusHelper.Annulee"/>.
    /// Cancellation restores product quantities from <c>commandes_details</c>.
    /// </summary>
    public async Task UpdateStatusAsync(int commandeId, string requestedTargetStatus, int? actingBusinessId, bool isAdmin)
    {
        var target = OrderStatusHelper.Normalize(requestedTargetStatus);
        if (target != OrderStatusHelper.Confirmee && target != OrderStatusHelper.Livree && target != OrderStatusHelper.Annulee)
            throw new Exception("Statut cible non reconnu.");

        await using var conn = await DatabaseService.GetConnectionAsync();
        await using var tx = (MySqlTransaction)await conn.BeginTransactionAsync();
        try
        {
            const string readSql = "SELECT business_id, status FROM commandes WHERE id=@id LIMIT 1";
            int bizId;
            string current;
            await using (var readCmd = new MySqlCommand(readSql, conn, tx))
            {
                readCmd.Parameters.AddWithValue("@id", commandeId);
                await using var r = await readCmd.ExecuteReaderAsync();
                if (!await r.ReadAsync())
                    throw new Exception("Commande introuvable.");
                bizId = Convert.ToInt32(r["business_id"]);
                current = Convert.ToString(r["status"]) ?? OrderStatusHelper.EnAttente;
                await r.CloseAsync();
            }

            if (!isAdmin && (!actingBusinessId.HasValue || actingBusinessId.Value != bizId))
                throw new Exception("Vous ne pouvez pas modifier cette commande.");

            var curNorm = OrderStatusHelper.Normalize(current);
            if (!OrderStatusHelper.CanTransition(curNorm, target, out var msg))
                throw new Exception(msg ?? "Transition refusée.");

            if (target == OrderStatusHelper.Annulee)
                await RestoreStockFromOrderDetailsAsync(conn, tx, commandeId);

            const string updSql = "UPDATE commandes SET status=@st WHERE id=@id AND status=@cur";
            await using (var u = new MySqlCommand(updSql, conn, tx))
            {
                u.Parameters.AddWithValue("@st", target);
                u.Parameters.AddWithValue("@id", commandeId);
                u.Parameters.AddWithValue("@cur", current);
                var n = await u.ExecuteNonQueryAsync();
                if (n <= 0)
                    throw new Exception("Impossible de mettre à jour le statut (déjà modifié ?).");
            }

            await tx.CommitAsync();
        }
        catch
        {
            await tx.RollbackAsync();
            throw;
        }
    }

    private static async Task RestoreStockFromOrderDetailsAsync(MySqlConnection conn, MySqlTransaction tx, int commandeId)
    {
        const string sel = "SELECT produit_id, quantite FROM commandes_details WHERE commande_id=@cid";
        var lines = new List<(int ProduitId, int Qty)>();
        await using (var selCmd = new MySqlCommand(sel, conn, tx))
        {
            selCmd.Parameters.AddWithValue("@cid", commandeId);
            await using var r = await selCmd.ExecuteReaderAsync();
            while (await r.ReadAsync())
                lines.Add((Convert.ToInt32(r["produit_id"]), Convert.ToInt32(r["quantite"])));
        }

        foreach (var (produitId, qty) in lines)
        {
            await using var up = new MySqlCommand(
                @"UPDATE produits
                  SET quantite = quantite + @q,
                      is_active = CASE WHEN quantite + @q > 0 THEN 1 ELSE is_active END
                  WHERE id = @pid",
                conn, tx);
            up.Parameters.AddWithValue("@q", qty);
            up.Parameters.AddWithValue("@pid", produitId);
            await up.ExecuteNonQueryAsync();
        }
    }

    public async Task<bool> DeleteAsync(int id)
    {
        await using var conn = await DatabaseService.GetConnectionAsync();
        await using var cmd = new MySqlCommand("DELETE FROM commandes WHERE id=@id", conn);
        cmd.Parameters.AddWithValue("@id", id);
        return await cmd.ExecuteNonQueryAsync() > 0;
    }
}
