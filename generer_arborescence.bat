@echo off
REM =====================================================
REM  Script pour generer l arborescence du projet Doctolight
REM =====================================================

REM Aller dans le dossier du projet
cd /d "D:\xampp\htdocs\DoctoLight"

REM Generer l arborescence avec les fichiers et la sauvegarder dans un fichier texte
tree /F > arborescence_Doctorlight.txt

echo ============================================
echo  Arborescence generee avec succes !
echo  Fichier : arborescence_Doctorlight.txt
echo ============================================

pause
