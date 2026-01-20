<?php
require_once dirname(__DIR__) . '/database.php';

// 1.  Récupération des données
$pays_result = $pdo->query("SELECT id, nom FROM pays ORDER BY nom")->fetchAll();

$query = "SELECT s.*, p.nom as pays_nom 
          FROM soumissionnaire s 
          LEFT JOIN pays p ON s.paysID = p.id 
          ORDER BY s.nom_entreprise ASC";
$fournisseurs = $pdo->query($query)->fetchAll();
?>

<div class="content-header">
    <h2><i class="fas fa-truck"></i> Gestion des Fournisseurs</h2>
</div>

<div class="card">
    <div class="card-header">
        <i class="fas fa-plus-circle"></i> <span id="formTitle">Ajouter un Fournisseur</span>
    </div>
    <div class="card-body">
        <form id="fournisseurForm" action="process.php" method="POST" novalidate>
            <input type="hidden" name="form_type" value="fournisseur">
            <input type="hidden" name="id" id="fournisseurId" value="">
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Nom de l'entreprise <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="nom" name="nom" placeholder="Min. 3 caractères" required>
                    <div class="invalid-feedback"></div>
                </div>
                
                <div class="col-md-6 mb-3">
                    <label class="form-label">Email <span class="text-danger">*</span></label>
                    <input type="email" class="form-control" id="email" name="email" placeholder="exemple@domaine.com" required>
                    <div class="invalid-feedback"></div>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Téléphone <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text">+</span>
                        <input type="tel" class="form-control" id="telephone" name="telephone" placeholder="213..." required>
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                
                <div class="col-md-6 mb-3">
                    <label class="form-label">Pays <span class="text-danger">*</span></label>
                    <select class="form-select" id="pays" name="pays" required>
                        <option value="">Sélectionner...</option>
                        <?php foreach ($pays_result as $p): ?>
                            <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['nom']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            
            <div class="mb-3">
                <label class="form-label">Adresse <span class="text-danger">*</span></label>
                <textarea class="form-control" id="adresse" name="adresse" rows="2" placeholder="Adresse complète" required></textarea>
            </div>
            
            <div class="d-flex gap-2">
                <button type="submit" id="submitBtn" class="btn btn-primary ajouter">
                    <i class="fas fa-save"></i> Enregistrer
                </button>
                <button type="button" id="cancelEdit" class="btn btn-secondary d-none">Annuler</button>
            </div>
        </form>
    </div>
</div>

<div class="card mt-4">
    <div class="card-header">
        <i class="fas fa-table"></i> Liste des Fournisseurs
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>Entreprise</th>
                        <th>Email</th>
                        <th>Téléphone</th>
                        <th>Pays</th>
                        <th>Adresse</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($fournisseurs)): ?>
                        <tr><td colspan="6" class="text-center py-3">Aucun fournisseur enregistré.</td></tr>
                    <?php else: ?>
                        <?php foreach ($fournisseurs as $f): ?>
                        <tr>
                            <td><strong><?= htmlspecialchars($f['nom_entreprise']) ?></strong></td>
                            <td><strong><?= htmlspecialchars($f['email']) ?></strong></td>
                            <td><strong><?= htmlspecialchars($f['telephone']) ?></strong></td>
                            <td><?= htmlspecialchars($f['pays_nom']) ?></td>
                            <td><?= htmlspecialchars($f['adresse']) ?></td>
                            <td>
                                <button class="btn btn-sm eye text-white edit-fournisseur" 
                                        data-fournisseur='<?= htmlspecialchars(json_encode($f), ENT_QUOTES, 'UTF-8') ?>'>
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="btn btn-sm btn-danger delete-fournisseur" 
                                        data-id="<?= $f['id'] ?>" 
                                        data-nom="<?= htmlspecialchars($f['nom_entreprise']) ?>">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
const fournisseurForm = document.getElementById('fournisseurForm');

// Nettoyage téléphone
document.getElementById('telephone').addEventListener('input', function() {
    this.value = this.value.replace(/[^0-9]/g, '');
});
document.getElementById('nom').addEventListener('input', function() {
    this.value = this.value.replace(/[^a-zA-ZÀ-ÿ\s\-]/g, '');
});

// Soumission (Ajout / Modif) avec SweetAlert2
fournisseurForm.addEventListener('submit', async (e) => {
    e.preventDefault();
    fournisseurForm.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));

    try {
        const response = await fetch('process.php', { method: 'POST', body: new FormData(fournisseurForm) });
        const data = await response.json();

        if (data.ok) {
            Swal.fire({
                icon: 'success',
                title: 'Succès',
                text: 'Données enregistrées avec succès',
                timer: 1500,
                showConfirmButton: false,
                timerProgressBar: true
            }).then(() => location.reload());
        } else if (data.errors) {
            for (const [key, msg] of Object.entries(data.errors)) {
                const input = fournisseurForm.querySelector(`[name="${key}"]`);
                if (input) {
                    input.classList.add('is-invalid');
                    const feedback = input.closest('.mb-3, .input-group')?.querySelector('.invalid-feedback');
                    if(feedback) feedback.textContent = msg;
                }
            }
        } else {
            Swal.fire('Erreur', data.message, 'error');
        }
    } catch (err) { 
        Swal.fire('Erreur', 'Impossible de joindre le serveur.', 'error'); 
    }
});

// Mode Modification
document.querySelectorAll('.edit-fournisseur').forEach(btn => {
    btn.addEventListener('click', function() {
        const f = JSON.parse(this.dataset.fournisseur);
        document.getElementById('formTitle').textContent = "Modifier : " + f.nom_entreprise;
        document.getElementById('fournisseurId').value = f.id;
        
        fournisseurForm.querySelector('[name="nom"]').value = f.nom_entreprise;
        fournisseurForm.querySelector('[name="email"]').value = f.email;
        fournisseurForm.querySelector('[name="telephone"]').value = f.telephone.replace('+', '');
        fournisseurForm.querySelector('[name="pays"]').value = f.paysID;
        fournisseurForm.querySelector('[name="adresse"]').value = f.adresse;

        document.getElementById('submitBtn').innerHTML = '<i class="fas fa-sync"></i> Mettre à jour';
        document.getElementById('cancelEdit').classList.remove('d-none');
        window.scrollTo({ top: 0, behavior: 'smooth' });
    });
});

document.getElementById('cancelEdit').addEventListener('click', () => location.reload());

// Suppression avec SweetAlert2 (Harmonisée)
document.querySelectorAll('.delete-fournisseur').forEach(btn => {
    btn.addEventListener('click', function() {
        const id = this.dataset.id;
        const nom = this.dataset.nom;

        Swal.fire({
            title: 'Êtes-vous sûr ?',
            text: `Supprimer le fournisseur "${nom}" ?`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Oui, supprimer !',
            cancelButtonText: 'Annuler',
            reverseButtons: false // Annuler à gauche, Supprimer à droite
        }).then(async (result) => {
            if (result.isConfirmed) {
                const fd = new FormData();
                fd.append('form_type', 'delete_fournisseur');
                fd.append('id', id);

                const res = await fetch('process.php', { method: 'POST', body: fd });
                const data = await res.json();
                if (data.ok) {
                    Swal.fire({
                        title: 'Supprimé !',
                        icon: 'success',
                        timer: 1500,
                        showConfirmButton: false,
                        timerProgressBar: true
                    }).then(() => location.reload());
                } else {
                    Swal.fire('Erreur', data.message, 'error');
                }
            }
        });
    });
});
</script>