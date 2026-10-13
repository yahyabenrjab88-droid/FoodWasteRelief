# 🍃 FoodWasteRelief

**FoodWasteRelief** est une plateforme complète de lutte contre le gaspillage alimentaire, composée de deux applications complémentaires.

---

## 📁 Structure du Projet

```
FoodWasteRelief-Complet/
├── desktop-app/     → Application de gestion (C# / WPF)
└── web-app/         → Plateforme web (PHP MVC)
```

---

## 🖥️ Application Desktop (`desktop-app/`)

Application **WPF C#** destinée à la gestion interne du système.

### Technologies
- C# / WPF (.NET)
- MySQL (via `foodwaste_db`)

### Lancement
1. Ouvrir `FoodWasteRelief.csproj` dans Visual Studio
2. Configurer la connexion DB dans `App.config`
3. Lancer avec `F5`

---

## 🌐 Application Web (`web-app/`)

Plateforme PHP MVC accessible aux **clients**, **entreprises**, **associations** et **administrateurs**.

### Technologies
- PHP 8+ (MVC custom)
- MySQL
- Bootstrap 5 + Lucide Icons

### Fonctionnalités
- 🛒 Catalogue de produits anti-gaspillage
- 🤝 Système de dons entre entreprises et associations
- 📊 Tableaux de bord (client, business, association, admin)
- 📈 Statistiques d'impact (CO2, repas sauvés)

### Installation locale (XAMPP)
1. Placer `web-app/` dans `C:\xampp\htdocs\projet1\web\`
2. Importer `foodwaste_db.sql` dans phpMyAdmin
3. Configurer `config/database.php`
4. Accéder via `http://localhost/projet1/web/`

---

## 🗄️ Base de données

Les scripts SQL se trouvent dans `desktop-app/` :
| Fichier | Description |
|---------|-------------|
| `foodwaste_db.sql` | Structure de base |
| `foodwaste_db_with_data.sql` | Structure + données de test |
| `foodwaste_migrations_v2.sql` | Migrations v2 |

---

## 👥 Rôles Utilisateurs

| Rôle | Accès |
|------|-------|
| **Client** | Catalogue, commandes, dashboard |
| **Business** | Gestion produits, dons, commandes |
| **Association** | Demande de dons |
| **Admin** | Gestion complète de la plateforme |

---

*Projet académique — FoodWasteRelief © 2026*
