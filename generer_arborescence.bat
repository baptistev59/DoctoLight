@echo off
REM =====================================================
REM  Script pour générer l'arborescence du projet Doctolight
REM =====================================================

REM Aller dans le dossier du projet (remplace le chemin ci-dessous si nécessaire)
cd /d "D:\xampp\htdocs\DoctoLight"

REM Générer l'arborescence avec les fichiers et la sauvegarder dans un fichier texte
tree /F > arborescence_Doctorlight.txt

echo ============================================
echo  Arborescence générée avec succès !
echo  Fichier : arborescence_Doctorlight.txt
echo ============================================

pause
