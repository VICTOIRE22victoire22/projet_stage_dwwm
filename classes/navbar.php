<?php

class Navbar
{
    // Déclaration des variables de la barre de navigation : logo, liens, URL de déconnexion et nom utilisateur
    private string $brand;
    private array $links = [];
    private ?string $logoutUrl = null;
    private ?string $username = null;

    // Initialise le logo dans la barre de navigation
    public function __construct(string $brand = "LOGO")
    {
        $this->brand = $brand;
    }

    // Ajoute un lien à la barre de navigation
    public function addLink(string $label, string $url = "#", array $submenu = []): void
    {
        $this ->links[$label] = $submenu ?: $url;
    }

    // Définit l’URL de déconnexion et le nom de l’utilisateur
    public function setLogout(string $url, string $username): void
    {
        $this->logoutUrl = $url;
        $this->username = $username;
    }

    private function renderMenu(array $items): string
    {
        $html = '<ul>';

        foreach ($items as $label => $value) {
            if (is_array($value)) {
                $html .= '<li><a href="#">' . htmlspecialchars($label) . '</a>';
                $html .= $this->renderMenu($value);
                $html .= '</li>';
            } else {
                $html .= '<li><a href="' . htmlspecialchars($value) . '">' . htmlspecialchars($label) . '</a></li>';
            }
        }

        $html .= '</ul>';
        return $html;
    }

    public function render(): string
    {
        $html = '<nav class="navbar">';
        $html .= '<a href="index.php" class="brand">' . htmlspecialchars($this->brand) . '</a>';
        $html .= $this->renderMenu($this->links);

        if ($this->username && $this->logoutUrl) {
            $html .= '<ul><li>Bonjour, ' . htmlspecialchars($this->username) .
                ' <a href="' . htmlspecialchars($this->logoutUrl) . '">Déconnexion</a></li></ul>';
        }

        $html .= '</nav>';

        return $html;
    }
}
