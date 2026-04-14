<?php
namespace App\Http\Controllers\Superadmin;

use App\Http\Controllers\Controller;
use App\Models\MacMachine;
use App\Models\Message;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class MacMachineController extends Controller
{
    public function show(MacMachine $macMachine): View
    {
        $macMachine->load('project.client');
        $pendingCount = Message::whereHas('conversation.projectMember.agent', function ($q) use ($macMachine) {
            $q->where('mac_machine_id', $macMachine->id);
        })->where('status', 'pending')->count();

        $recentMessages = Message::whereHas('conversation.projectMember.agent', function ($q) use ($macMachine) {
            $q->where('mac_machine_id', $macMachine->id);
        })->latest('created_at')->limit(10)->get();

        return view('superadmin.mac-machines.show', compact('macMachine', 'pendingCount', 'recentMessages'));
    }

    public function edit(MacMachine $macMachine): View
    {
        return view('superadmin.mac-machines.edit', compact('macMachine'));
    }

    public function update(Request $request, MacMachine $macMachine): RedirectResponse
    {
        $data = $request->validate([
            'name'    => 'required|string|max:100',
            'profile' => 'nullable|string|max:100',
        ]);
        $macMachine->update($data);
        return redirect()->route('admin.mac-machines.show', $macMachine)
            ->with('success', 'Machine mise à jour.');
    }

    public function destroy(MacMachine $macMachine): RedirectResponse
    {
        $projectId = $macMachine->project_id;
        $macMachine->delete();
        return redirect()->route('admin.projects.show', $projectId)
            ->with('success', 'Machine supprimée.');
    }

    public function regenerateToken(MacMachine $macMachine): RedirectResponse
    {
        $newToken = $macMachine->regenerateToken();
        return redirect()->route('admin.mac-machines.show', $macMachine)
            ->with('success', "Nouveau token : {$newToken}");
    }

    public function downloadSetupGuide(MacMachine $macMachine): \Symfony\Component\HttpFoundation\Response
    {
        $machineName = $macMachine->name;

        $guide = <<<TEXT
SPQ DAEMON — PROCÉDURE D'INSTALLATION SUR MAC MINI
Généré pour : {$machineName}
====================================================


PRÉREQUIS
---------

1. Python 3 installé
   Vérifier : python3 --version
   Si absent : télécharger sur python.org ou via Homebrew : brew install python3

2. Clé SSH configurée pour SiteGround
   La clé privée doit être présente sur le Mac Mini (ex: ~/.ssh/id_rsa ou ~/.ssh/id_ed25519).
   La clé publique correspondante doit être importée dans SiteGround :
     Site Tools → Devs → SSH Keys → Importer

   Tester la connexion sans mot de passe :
     ssh -p 18765 u685-ubgnyznjtpvj@ssh.spq.app

   Si ça demande un mot de passe → la clé n'est pas correctement importée dans SiteGround.

3. OpenClaw installé et configuré
   Vérifier :
     openclaw --version
     openclaw agents list --json
   → Doit lister les agents/profils disponibles.


INSTALLATION DU DAEMON
----------------------

4. Dans l'admin SPQ, aller sur la page de cette machine ({$machineName})
   et cliquer "Télécharger le launcher" → enregistrer le fichier .command.

5. Dans le Terminal, rendre le fichier exécutable :
     chmod +x ~/Downloads/spq_*.command

6. Double-cliquer sur le fichier .command dans le Finder.
   Un Terminal s'ouvre et le script :
     - Copie spq_daemon.py dans ~/.spq/
     - Installe pymysql si absent (pip3 install pymysql)
     - Ouvre un tunnel SSH vers ssh.spq.app:18765
     - Se connecte directement à MySQL via le tunnel
     - Démarre la boucle de traitement des messages


VÉRIFICATION
------------

Dans le Terminal, les logs s'affichent en temps réel. On doit voir :

  2025-01-01 00:00:00 [INFO] Opening SSH tunnel → u685-ubgnyznjtpvj@ssh.spq.app:18765 ...
  2025-01-01 00:00:00 [INFO] Tunnel SSH actif (PID 12345)
  2025-01-01 00:00:00 [INFO] MySQL connecté.
  2025-01-01 00:00:00 [INFO] Machine ID : X

Dans l'admin SPQ, la machine doit passer en statut vert (online) dans les 30 secondes.

Pour voir les logs en dehors du Terminal :
  tail -f /tmp/SPQ_daemon.log


DÉMARRAGE AUTOMATIQUE AU BOOT (optionnel)
-----------------------------------------

Pour que le daemon redémarre automatiquement à chaque démarrage du Mac Mini :

1. Déplacer le fichier .command dans un dossier permanent, ex :
     mv ~/Downloads/spq_*.command ~/spq_daemon.command

2. Créer le LaunchAgent (copier-coller en une seule fois dans Terminal) :

   cat > ~/Library/LaunchAgents/app.spq.daemon.plist << 'EOF'
   <?xml version="1.0" encoding="UTF-8"?>
   <!DOCTYPE plist PUBLIC "-//Apple//DTD PLIST 1.0//EN" "http://www.apple.com/DTDs/PropertyList-1.0.dtd">
   <plist version="1.0">
   <dict>
       <key>Label</key><string>app.spq.daemon</string>
       <key>ProgramArguments</key>
       <array>
           <string>/bin/bash</string>
           <string>/Users/VOTRE_NOM/spq_daemon.command</string>
       </array>
       <key>RunAtLoad</key><true/>
       <key>KeepAlive</key><true/>
       <key>StandardOutPath</key><string>/tmp/SPQ_daemon.log</string>
       <key>StandardErrorPath</key><string>/tmp/SPQ_daemon.log</string>
   </dict>
   </plist>
   EOF

   IMPORTANT : remplacer /Users/VOTRE_NOM par le chemin réel vers votre fichier .command
   (vérifier avec : echo ~/spq_daemon.command)

3. Activer le LaunchAgent :
     launchctl load ~/Library/LaunchAgents/app.spq.daemon.plist

4. Pour désactiver le démarrage automatique :
     launchctl unload ~/Library/LaunchAgents/app.spq.daemon.plist


EN CAS DE PROBLÈME
------------------

Symptôme                              Cause probable
--------------------------------------+-------------------------------------------
ssh: connect to host ssh.spq.app      Clé SSH pas importée dans SiteGround, ou
                                       mauvais hostname/port
MySQL connection error                 Tunnel pas encore établi — attendre 5s,
                                       ou identifiants DB incorrects
Token introuvable en base              Mauvais token — retélécharger le launcher
openclaw introuvable                   OpenClaw pas dans le PATH
                                       Vérifier : which openclaw
Machine reste offline dans l'admin     Vérifier les logs : tail -f /tmp/SPQ_daemon.log
Réponse vide de l'agent                Problème de config du profil OpenClaw
                                       Tester manuellement :
                                       openclaw agent --profile NOM --message "test"

Pour toute question : admin SPQ → page de la machine → section logs.
TEXT;

        $filename = 'spq_setup_' . \Str::slug($macMachine->name) . '.txt';

        return response($guide, 200, [
            'Content-Type'        => 'text/plain; charset=utf-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    public function downloadLauncher(MacMachine $macMachine): \Symfony\Component\HttpFoundation\Response
    {
        $daemonScript = file_get_contents(base_path('daemon/spq_daemon.py'));
        $machineName  = $macMachine->name;
        $token        = $macMachine->token;

        // DB credentials embedded in the launcher
        $dbName = config('database.connections.mysql.database');
        $dbUser = config('database.connections.mysql.username');
        $dbPass = config('database.connections.mysql.password');

        $launcher = <<<BASH
#!/bin/bash
# SPQ Daemon Launcher — {$machineName}
# Double-cliquez sur ce fichier pour démarrer le daemon.
# Tout est automatique — aucun autre fichier nécessaire.

set -e

DIR="\$HOME/.spq"
mkdir -p "\$DIR"

# Écriture du daemon (version embarquée)
cat > "\$DIR/spq_daemon.py" << 'SPQ_DAEMON_EOF'
{$daemonScript}
SPQ_DAEMON_EOF

# Installer/mettre à jour pymysql + cryptography (requis pour MySQL 8.0)
echo "Mise à jour des dépendances Python..."
python3 -m pip install --upgrade pymysql cryptography 2>&1 | grep -v "already satisfied"

clear
echo "╔══════════════════════════════════════════╗"
echo "║  SPQ Daemon — {$machineName}"
echo "╚══════════════════════════════════════════╝"
echo ""
echo "  Dossier  : \$DIR"
echo "  Tunnel   : ssh.spq.app:18765 → MySQL:3306"
echo ""
echo "  Logs en temps réel ci-dessous."
echo "  Ctrl+C pour arrêter proprement."
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo ""

python3 "\$DIR/spq_daemon.py" \
    --token "{$token}" \
    --ssh-host "ssh.spq.app" \
    --ssh-port 18765 \
    --ssh-user "u685-ubgnyznjtpvj" \
    --db-name "{$dbName}" \
    --db-user "{$dbUser}" \
    --db-pass "{$dbPass}"

echo ""
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "  Daemon arrêté. Appuyez sur Entrée pour fermer."
read
BASH;

        $filename = 'spq_' . \Str::slug($macMachine->name) . '.command';

        return response($launcher, 200, [
            'Content-Type'        => 'application/octet-stream',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }
}
