# Frontend assets: versions, hashes and load order

Reference for the libraries copied into `public/` by `npm run assets` (rules and deploy exclusions live in `AGENTS.md` › Dependencias frontend).

| Archivo en `public/`                                            | Origen                                               | sha384                                                             |
| --------------------------------------------------------------- | ---------------------------------------------------- | ------------------------------------------------------------------ |
| `js/lib/adminlte/adminlte.min.js`                               | AdminLTE 4.10.1, Composer (`almasaeed2010/adminlte`) | `shXf1qVpiVWOxvoS++WEJAWh0pwODvYHl7MGqTbmmaNdZASt7JJxS3P4RxBN/lp3` |
| `js/lib/bootstrap/bootstrap.bundle.min.js`                      | Bootstrap 5.3.8, npm                                 | `FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI` |
| `js/lib/overlayscrollbars/overlayscrollbars.browser.es6.min.js` | OverlayScrollbars 2.16.0, npm                        | `8Ko/rTyh+vMEKn5C3G3cFsAqjuuMebZHqRNfP2OgbQtJDDMOWXmWrP6OhJQaMXyk` |
| `js/plugins/tabulator/tabulator.min.js`                         | Tabulator 6.6.1, npm                                 | `G2ks3Wc8ERwZQbUnD+D54IZmFUlgMENIYP7tgDtz5ANc7yNyu+z0+4qn7Iit8jsH` |
| `js/plugins/tom-select/tom-select.complete.min.js`              | Tom Select 2.6.2, npm                                | `1mYKSrq1Nu5YJmWrIU9cvwWQlUyyukJJM9XMkAxY03nb/T69CK+Sn7rjFxVU3SSM` |
| `js/plugins/chart/chart.umd.min.js`                             | Chart.js 4.5.1, npm                                  | `jb8JQMbMoBUzgWatfe6COACi2ljcDdZQ2OxczGA3bGNeWe+6DChMTBJemed7ZnvJ` |
| `js/plugins/sweetalert2/sweetalert2.min.js`                     | SweetAlert2 11.26.25, npm                            | `hW8ZCQHtRH+nVOAkHZ4amZvYsAtKn1ZOvMV6dNag1Rb1thWmLZMBKTRxFV0cOxiK` |
| `js/plugins/utils/xlsx.full.min.js`                             | SheetJS 0.20.3, `cdn.sheetjs.com` (**a mano**)       | `EnyY0/GSHQGSxSgMwaIPzSESbqoOLSexfnSMN2AP+39Ckmn92stwABZynq1JyzdT` |
| `js/plugins/utils/jspdf.umd.min.js`                             | jsPDF 4.2.1, npm                                     | `qovJwSBbRDPP5cEjCp8S0UP66wrvnjaa60XMOGzTNanrThcrGfXfnZkvgY8N1KT3` |
| `js/plugins/utils/jspdf.plugin.autotable.min.js`                | jspdf-autotable 5.0.9, npm                           | `A77YsHWitrPuAj8E0AuB033EXzymUxIajnWKPzrrzqFoYYSCEpFennDUjMG2R1L3` |

- **Orden de carga.** `header.php`: `overlayscrollbars.min.css` → `bootstrap-icons.min.css` → `adminlte.min.css` → `ui-components.css` → `dark-mode.css` → CSS de plugins → `sweetalert2.min.css` → CSS de módulo. `footer.php`: `bootstrap.bundle` → `adminlte` → `overlayscrollbars` → `sweetalert2` → `http.js`, `common-utils.js`, `form-validator.js` → JS de plugins pedidos por el controlador → `ui-components.js` → `sweetalert-utils.js` → `table-utils.js` → `theme-toggle.js` → JS del módulo. Los layouts de autenticación cargan solo lo que usan (`auth.php`).
- **Cadena de suministro:** SweetAlert2 desde la 11.4.9 incluye código «protestware» limitado a dominios `.ru`/`.su`/`.рф`; no afecta a una instalación fuera de esos dominios, pero conviene saberlo si se despliega allí. El sha384 de arriba sirve para comprobar que `npm run assets` no cambió un archivo sin querer.
