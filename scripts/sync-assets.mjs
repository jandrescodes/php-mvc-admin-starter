#!/usr/bin/env node
// Copia los assets de frontend de node_modules/ y vendor/ a public/ sin transformarlos.
// Paso de desarrollo (npm run assets), no un build: lo copiado se versiona.
import { copyFileSync, existsSync, mkdirSync, readdirSync, readFileSync } from 'node:fs';
import { dirname, join, resolve } from 'node:path';
import { fileURLToPath } from 'node:url';

const root = resolve(dirname(fileURLToPath(import.meta.url)), '..');
const nm = (p) => join('node_modules', p);
const lte = (p) => join('vendor/almasaeed2010/adminlte/dist', p);

// [origen, destino] relativos a la raíz del repositorio.
const files = [
    [lte('css/adminlte.min.css'), 'public/css/lib/adminlte/adminlte.min.css'],
    [lte('js/adminlte.min.js'), 'public/js/lib/adminlte/adminlte.min.js'],
    [nm('bootstrap/dist/js/bootstrap.bundle.min.js'), 'public/js/lib/bootstrap/bootstrap.bundle.min.js'],
    [nm('overlayscrollbars/styles/overlayscrollbars.min.css'), 'public/css/lib/overlayscrollbars/overlayscrollbars.min.css'],
    [nm('overlayscrollbars/browser/overlayscrollbars.browser.es6.min.js'), 'public/js/lib/overlayscrollbars/overlayscrollbars.browser.es6.min.js'],
    [nm('bootstrap-icons/font/bootstrap-icons.min.css'), 'public/css/lib/bootstrap-icons/bootstrap-icons.min.css'],
    [nm('tabulator-tables/dist/js/tabulator.min.js'), 'public/js/plugins/tabulator/tabulator.min.js'],
    [nm('tabulator-tables/dist/css/tabulator_bootstrap5.min.css'), 'public/css/plugins/tabulator/tabulator_bootstrap5.min.css'],
    [nm('tom-select/dist/js/tom-select.complete.min.js'), 'public/js/plugins/tom-select/tom-select.complete.min.js'],
    [nm('tom-select/dist/css/tom-select.bootstrap5.min.css'), 'public/css/plugins/tom-select/tom-select.bootstrap5.min.css'],
    [nm('xlsx/dist/xlsx.full.min.js'), 'public/js/plugins/utils/xlsx.full.min.js'],
    [nm('jspdf/dist/jspdf.umd.min.js'), 'public/js/plugins/utils/jspdf.umd.min.js'],
    [nm('jspdf-autotable/dist/jspdf.plugin.autotable.min.js'), 'public/js/plugins/utils/jspdf.plugin.autotable.min.js'],
    [nm('chart.js/dist/chart.umd.min.js'), 'public/js/plugins/chart/chart.umd.min.js'],
    [nm('sweetalert2/dist/sweetalert2.min.js'), 'public/js/plugins/sweetalert2/sweetalert2.min.js'],
    [nm('sweetalert2/dist/sweetalert2.min.css'), 'public/css/plugins/sweetalert2/sweetalert2.min.css'],
];

// Las fuentes se copian con el mismo nombre de carpeta que el CSS referencia (./fonts/).
const fontsDir = nm('bootstrap-icons/font/fonts');
for (const name of readdirSync(join(root, fontsDir))) {
    files.push([join(fontsDir, name), `public/css/lib/bootstrap-icons/fonts/${name}`]);
}

let copied = 0;
let unchanged = 0;
for (const [from, to] of files) {
    const src = join(root, from);
    const dst = join(root, to);
    if (!existsSync(src)) {
        console.error(`Falta el origen ${from}. Ejecuta "composer install" y "npm ci".`);
        process.exit(1);
    }
    if (existsSync(dst) && readFileSync(src).equals(readFileSync(dst))) {
        unchanged++;
        continue;
    }
    mkdirSync(dirname(dst), { recursive: true });
    copyFileSync(src, dst);
    copied++;
    console.log(`copiado ${to}`);
}
console.log(`${copied} copiados, ${unchanged} sin cambios.`);
