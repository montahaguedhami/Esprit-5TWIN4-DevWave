# TuniBeach APAL catalogue

Source: the official APAL page, [Liste des plages en Tunisie](https://www.environnement.gov.tn/lagence-de-protection-et-damenagement-du-littoral-apal), workbook `P_liste_des_plages.xls`.

The workbook was retrieved on 2026-10-19. Its catalogue sheet contains 129 beach-name records in 11 governorates. This import keeps only the source row number, governorate name, and beach name in `apal_beaches.csv`.

The source workbook's `Long.` and `Larg` fields are dimensions: the sample values and `Sup. m2` area show they are length and width, not GPS coordinates. They are deliberately omitted. The workbook does not provide bathing decisions or water measurements. Imported beach coordinates remain null/unverified and bathing status remains `unknown` until supported by a separate trusted source.

To import this catalogue into the configured database, run only the dedicated additive seeder:

```powershell
php artisan db:seed --class=Database\Seeders\TuniBeachApalSeeder
```

The seeder uses `firstOrCreate`, so rerunning it does not duplicate or overwrite records. It does not delete data or modify existing AquaSecure tables.

## Historical bathing advisory points

`historical_advisory_points.csv` contains 48 named localizations and one unidentified Bizerte point from the June 20, 2026 report. The named points have the historical status `restricted`; the unidentified point has `unknown`. The national total and governorate distribution were reported by [TAP](https://www.tap.info.tn/fr/Portail-Titres-de-l-actualit%C3%A9/20310648-interdiction-de). This is a transcription of the detailed list provided for this project, not the Ministry's original point-level export, and it does not confirm current bathing status.

Replace the dated historical point list separately from current bathing decisions:

```powershell
php artisan db:seed --class=Database\Seeders\TuniBeachHistoricalAdvisorySeeder
```

These rows replace the existing June 20, 2026 historical rows in `tunibeach_historical_advisory_points`, not current `safe`, `restricted`, or `prohibited` decisions. The citizen detail shows the dated governorate list as context, warns that it cannot establish a selected beach's identity or current status, and links the source. Current statuses remain `unknown` unless separately supported by a verified decision with a valid period.
