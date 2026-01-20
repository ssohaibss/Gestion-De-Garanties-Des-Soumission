<?php
/**
 * Helper function to get Bootstrap badge class based on status
 */
function getStatusBadgeClass($statut) {
    $statut = strtolower($statut);
    
    if (strpos($statut, 'actif') !== false || strpos($statut, 'active') !== false) {
        return 'bg-success';
    } elseif (strpos($statut, 'expire') !== false || strpos($statut, 'expired') !== false) {
        return 'bg-danger';
    } elseif (strpos($statut, 'pending') !== false || strpos($statut, 'en attente') !== false) {
        return 'bg-warning text-dark';
    } elseif (strpos($statut, 'cancelled') !== false || strpos($statut, 'annulé') !== false) {
        return 'bg-secondary';
    }
    
    return 'bg-secondary';
}

/**
 * Format date to French format
 */
function formatDateFR($date) {
    if (!$date) return 'N/A';
    return date('d/m/Y', strtotime($date));
}

/**
 * Format amount with currency
 */
function formatAmount($amount, $currency = 'DZD') {
    return number_format($amount, 2, ',', ' ') . ' ' . htmlspecialchars($currency);
}

/**
 * Calculate remaining days
 */
function getDaysRemaining($expirationDate) {
    $now = new DateTime();
    $expiry = new DateTime($expirationDate);
    $interval = $now->diff($expiry);
    
    if ($interval->invert) {
        return -$interval->days;
    }
    return $interval->days;
}

/**
 * Get badge for days remaining
 */
function getDaysRemainingBadge($days) {
    if ($days < 0) {
        return '<span class="badge bg-danger">Expirée</span>';
    } elseif ($days <= 30) {
        return '<span class="badge bg-warning text-dark">' . $days . ' jours</span>';
    } else {
        return '<span class="badge bg-success">' . $days . ' jours</span>';
    }
}





?>

 <script>

    /*                  
                                            not needed (bad for banque form)



// numbers restriction 
document.getElementById('nom').addEventListener('input', function (e) {
    // Replaces any character that is NOT a letter, space, or hyphen with an empty string
    this.value = this.value.replace(/[^a-zA-ZÀ-ÿ\s\-]/g, '');});

    */
</script>
