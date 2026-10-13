using System;
using System.Windows;
using FoodWasteRelief.Services;
using MySql.Data.MySqlClient;

namespace FoodWasteRelief.Views.Details;

public partial class ContactRequestDetailWindow : Window
{
    private readonly int _id;
    private readonly bool _markRead;
    public bool MarkedReadNow { get; private set; }
    public ContactRequestDetailWindow(int id, bool markReadOnOpen) { _id = id; _markRead = markReadOnOpen; InitializeComponent(); Loaded += LoadedAsync; }
    private async void LoadedAsync(object sender, RoutedEventArgs e)
    {
        try
        {
            await using var conn = await DatabaseService.GetConnectionAsync();
            if (_markRead)
            {
                await using var up = new MySqlCommand("UPDATE contact_requests SET is_read=1 WHERE id=@id", conn);
                up.Parameters.AddWithValue("@id", _id);
                MarkedReadNow = await up.ExecuteNonQueryAsync() > 0;
            }
            const string sql = "SELECT sender_name, sender_role, subject, body, sent_at, is_read FROM contact_requests WHERE id=@id LIMIT 1";
            await using var cmd = new MySqlCommand(sql, conn); cmd.Parameters.AddWithValue("@id", _id);
            await using var r = await cmd.ExecuteReaderAsync(); if (!await r.ReadAsync()) { Close(); return; }
            SenderText.Text = Convert.ToString(r["sender_name"]) ?? ""; RoleBadge.Status = Convert.ToString(r["sender_role"]) ?? "";
            SubjectText.Text = Convert.ToString(r["subject"]) ?? ""; BodyText.Text = Convert.ToString(r["body"]) ?? "";
            SentText.Text = Convert.ToDateTime(r["sent_at"]).ToString("dd/MM/yyyy HH:mm");
            ReadBadge.Status = Convert.ToInt32(r["is_read"]) == 1 ? "Lu" : "Non lu";
        }
        catch (Exception ex) { MessageBox.Show(ex.Message, "Erreur", MessageBoxButton.OK, MessageBoxImage.Warning); Close(); }
    }
    private void Close_Click(object sender, RoutedEventArgs e) => Close();
}
