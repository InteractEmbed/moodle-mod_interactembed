# Moodle Marketplace listing material — InteractEmbed activity 1.0.0

This document prepares the listing content. It does not authorise or perform a
Moodle Marketplace submission.

## Listing metadata

- Plugin name: InteractEmbed activity
- Frankenstyle component: `mod_interactembed`
- Plugin type: Activity module (`mod`)
- Release: 1.0.0 stable, build `2026090701`
- Moodle support: 4.0–5.2 inclusive
- Licence: GNU GPL v3 or later
- Cost: Free
- Required plugins: None
- Optional companions: InteractEmbed Block and InteractEmbed Creator
- Repository: https://github.com/InteractEmbed/moodle-mod_interactembed
- Issue tracker: https://github.com/InteractEmbed/moodle-mod_interactembed/issues
- Security reports: https://github.com/InteractEmbed/moodle-mod_interactembed/security/advisories/new
- Suggested tags: HTML5, interactive content, embed, iframe, completion,
  grading, accessibility, multilingual

The Marketplace ZIP contains only Moodle's English source strings. Complete
French and international Spanish translations are supplied separately for AMOS
after approval. The included interactive guide remains natively trilingual.

## English

### Short description

Add validated HTML5 packages, privacy-aware external embeds or a local guide as
a secure Moodle activity with completion, grading and learner progress.

### Full description

InteractEmbed lets teachers add interactive HTML5 content as a standard Moodle
activity. Content can come from a ZIP archive or unpacked project selected with
Moodle File Manager, a supported web address or iframe, the included local
guide, or an optional InteractEmbed Creator publication.

Packages are validated before activation and delivered through short-lived
signed URLs without Moodle session cookies. Responsive display, cover images,
card and click-to-load modes, lazy loading, accessible full screen and
administrator-controlled sandbox profiles are included.

The activity integrates with Moodle Gradebook, completion, groups, course
reset, events, backup and restore, and the Privacy API. Compatible HTML5 content
can securely report explicit completion and bounded scores. The plugin sends no
automatic email or Moodle notification.

InteractEmbed works independently and requires no external account, API key,
subscription or additional Moodle plugin. External content contacts only the
provider selected by the teacher. The optional Creator integration verifies an
immutable publication and copies it into activity-owned storage, so the
activity remains usable if the source publication is later withdrawn.

### Installation

Upload the release ZIP from Site administration > Plugins > Install plugins,
or copy the `interactembed` directory to `mod/interactembed`, then complete the
standard Moodle upgrade. No Composer, command-line or core modification is
required. Review the InteractEmbed administration settings before permitting
remote providers or assigning the Trusted package capability.

### Release notes

First stable release. Validated on Moodle 4.0 through 5.2, MariaDB and
PostgreSQL, plus functional Moodle 5.2 and IOMAD/Moodle 4.5 installations.
Includes secure package delivery, remote embeds, trilingual guide, responsive
presentation, grading, completion, progress reports, Privacy API and complete
backup/restore support.

## Français

### Description courte

Ajoutez des paquets HTML5 validés, des contenus externes respectueux de la vie
privée ou un guide local sous forme d’activité Moodle sécurisée avec achèvement,
évaluation et progression.

### Description complète

InteractEmbed permet aux enseignants d’ajouter du contenu HTML5 interactif sous
forme d’activité Moodle standard. La source peut être une archive ZIP ou un
projet décompressé choisi avec le gestionnaire de fichiers Moodle, une adresse
web ou un iframe autorisé, le guide local inclus, ou une publication provenant
du Creator InteractEmbed facultatif.

Les paquets sont validés avant leur activation et distribués avec des adresses
signées de courte durée, sans cookie de session Moodle. L’affichage adaptatif,
les images de couverture, les modes carte et chargement sur demande, le
chargement différé, le plein écran accessible et les profils de bac à sable
contrôlés par l’administration sont inclus.

L’activité s’intègre au carnet de notes, à l’achèvement, aux groupes, à la
réinitialisation du cours, aux événements, à la sauvegarde/restauration et à
l’API de confidentialité de Moodle. Un contenu compatible peut transmettre de
façon sécurisée un achèvement explicite et une note bornée. Le plugin n’envoie
aucun courriel ni aucune notification Moodle automatique.

InteractEmbed fonctionne de manière autonome, sans compte externe, clé API,
abonnement ou autre plugin obligatoire. Un contenu externe communique seulement
avec le fournisseur choisi par l’enseignant. L’intégration Creator facultative
vérifie une publication immuable et en fait une copie indépendante appartenant
à l’activité.

### Installation

Téléversez le ZIP depuis Administration du site > Plugins > Installer des
plugins, ou copiez le dossier `interactembed` dans `mod/interactembed`, puis
effectuez la mise à niveau Moodle standard. Composer, la ligne de commande et
les modifications du cœur Moodle ne sont pas nécessaires. Vérifiez les
réglages d’administration avant d’autoriser des fournisseurs distants ou
d’attribuer la capacité de paquet Fiable.

### Notes de version

Première version stable. Validée avec Moodle 4.0 à 5.2, MariaDB et PostgreSQL,
ainsi que dans des installations fonctionnelles Moodle 5.2 et IOMAD/Moodle 4.5.
Elle comprend la distribution sécurisée de paquets, les contenus externes, le
guide trilingue, l’affichage adaptatif, l’évaluation, l’achèvement, les rapports
de progression, l’API de confidentialité et la sauvegarde/restauration.

## Español

### Descripción breve

Añada paquetes HTML5 validados, contenidos externos respetuosos con la
privacidad o una guía local como actividad Moodle segura con finalización,
calificación y progreso del alumnado.

### Descripción completa

InteractEmbed permite al profesorado añadir contenido HTML5 interactivo como
una actividad Moodle estándar. La fuente puede ser un archivo ZIP o un proyecto
descomprimido seleccionado con el gestor de archivos de Moodle, una dirección
web o iframe permitido, la guía local incluida o una publicación del Creator
InteractEmbed opcional.

Los paquetes se validan antes de activarse y se sirven mediante direcciones
firmadas de corta duración sin cookies de sesión de Moodle. Incluye diseño
adaptable, imágenes de portada, modos tarjeta y carga bajo demanda, carga
diferida, pantalla completa accesible y perfiles de aislamiento controlados por
la administración.

La actividad se integra con el libro de calificaciones, la finalización, los
grupos, el reinicio del curso, los eventos, las copias de seguridad y
restauración y la API de privacidad de Moodle. El contenido compatible puede
comunicar de forma segura una finalización explícita y puntuaciones limitadas.
El plugin no envía correos ni notificaciones Moodle automáticas.

InteractEmbed funciona de forma independiente y no requiere cuenta externa,
clave API, suscripción ni otro plugin Moodle. El contenido externo solo contacta
con el proveedor elegido por el docente. La integración Creator opcional
verifica una publicación inmutable y la copia al almacenamiento propio de la
actividad.

### Instalación

Suba el ZIP desde Administración del sitio > Plugins > Instalar plugins, o
copie el directorio `interactembed` en `mod/interactembed`, y complete la
actualización estándar de Moodle. No requiere Composer, línea de comandos ni
modificaciones del núcleo. Revise los ajustes administrativos antes de permitir
proveedores remotos o asignar la capacidad de paquete Confiable.

### Notas de la versión

Primera versión estable. Validada en Moodle 4.0 a 5.2, MariaDB y PostgreSQL,
además de instalaciones funcionales Moodle 5.2 e IOMAD/Moodle 4.5. Incluye
entrega segura de paquetes, contenidos externos, guía trilingüe, presentación
adaptable, calificación, finalización, informes de progreso, API de privacidad
y copia de seguridad/restauración.

## Marketplace assets checklist

Prepare these screenshots before manual submission. Use demonstration data only
and do not include learner names, email addresses, tokens or private packages.

1. Activity chooser with the InteractEmbed icon.
2. Activity settings showing File Manager and cover-image guidance.
3. Responsive inline package display with the accessible full-screen control.
4. Compact card and click-to-load presentation.
5. Completion, Gradebook and group-aware progress report.
6. Site administration policies and sandbox defaults.
7. Optional Creator publication selector, clearly labelled as optional.

Recommended screenshot format: PNG or WebP, 1600 × 900 or larger, consistent
theme and language per screenshot set, with no browser chrome when practical.
