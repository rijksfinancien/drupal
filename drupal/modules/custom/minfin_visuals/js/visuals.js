
(function ($, Drupal) {
    Drupal.behaviors.visuals = {
        attach: function (context, settings) {
            // START

            if (typeof (testlocal) == 'undefined') {
                var testlocal = false;
            }

            /*
            // initialize am4core
            // https://www.amcharts.com
            */
            am4core.useTheme(am4themes_animated);
            am4core.useTheme(am4themes_material);
            am4core.options.autoSetClassName = true;

            /*
            // setings for amcharts
            */
            am4core.settings = function (options) {
                for (let o in options) {
                    switch (o) {
                        case 'colors':
                            chart_colors = [];
                            for (let c in options[o]) {
                                chart_colors.push(am4core.color(options[o][c]));
                            }
                            break;
                        case 'delay':
                            _minfin_charts_options[o] = parseInt(options[o]);
                            break;
                        case 'inactive_color':
                        case 'stroke_color':
                            _minfin_charts_options[o] = am4core.color(options[o]);
                            break;
                        case 'stroke_width':
                        case 'dimmed_opacity':
                            _minfin_charts_options[o] = parseFloat(options[o]);
                            break;
                        case 'anchor':
                            minfin_api.anchor = options[o];
                            break;
                        case 'font':
                            _minfin_charts_options[o] = options[o];
                            break;
                    }
                }
            }
            var _minfin_charts_options = {
                delay: 250,
                inactive_color: am4core.color('#e6e6e6'),
                stroke_width: 0.5,
                stroke_color: am4core.color('#fff'),
                dimmed_opacity: 0.7,
                font_: 'trebuchet'
            };

            // pattern text
            var patternTxt = 'Gearceerde onderdelen betreffen een samenvoeging, splitsing of verhuizing t.o.v. andere jaren. Alle gerelateerde onderdelen zijn in de visual uitgelicht.';

            /*
            // set color series
            // provide enough colours, the list will repeat itself if needed
            */
            var minfin_colors = [
                "#d52b1e",
                "#e17000",
                "#ffb612",
                "#f9e11e",
                "#72be19",
                "#39870c",
                "#76d2b6",
                "#007bc7",
                "#01689b",
                "#42145f",
                "#a90061",
                "#ca005d"
            ];
            var minfin_color_names = {
                "red": "#d52b1e",
                "orange": "#e17000",
                "gold": "#ffb612",
                "darkyellow": "#ffb612",
                "yellow": "#f9e11e",
                "green": "#72be19",
                "darkgreen": "#39870c",
                "emerald": "#39870c",
                "mint": "#76d2b6",
                "blue": "#007bc7",
                "denim": "#01689b",
                "darkblue": "#01689b",
                "violet": "#42145f",
                "purple": "#42145f",
                "megenta": "#a90061",
                "darkpink": "#a90061",
                "pink": "#ca005d",
                "black": "#000000",
                "white": "#ffffff"
            };
            // https://base64.guru/converter/encode/image/svg
            var minfin_patterns = {
                '255255255': 'PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0iVVRGLTgiPz4KPHN2ZyB3aWR0aD0iMTVweCIgaGVpZ2h0PSIxNXB4IiB2aWV3Qm94PSIwIDAgMTUgMTUiIHZlcnNpb249IjEuMSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIiB4bWxuczp4bGluaz0iaHR0cDovL3d3dy53My5vcmcvMTk5OS94bGluayI+CiAgICA8dGl0bGU+R3JvdXAgOCBDb3B5PC90aXRsZT4KICAgIDxnIGlkPSJGYXNlLTItdmVydm9sZyIgc3Ryb2tlPSJub25lIiBzdHJva2Utd2lkdGg9IjEiIGZpbGw9Im5vbmUiIGZpbGwtcnVsZT0iZXZlbm9kZCI+CiAgICAgICAgPGcgaWQ9Ikdyb3VwLTgtQ29weSI+CiAgICAgICAgICAgIDxwb2x5Z29uIGlkPSJNYXNrLUNvcHkiIGZpbGw9IiNGRkZGRkYiIHBvaW50cz0iLTguMDY2NDY0MTZlLTE0IDAgMTUgMCAxNSAxNSAwIDE1Ij48L3BvbHlnb24+CiAgICAgICAgICAgIDxwYXRoIGQ9Ik00LDAgTDQsMTUgTDIsMTUgTDIsMCBMNCwwIFogTTksMCBMOSwxNSBMNywxNSBMNywwIEw5LDAgWiBNMTQsMCBMMTQsMTUgTDEyLDE1IEwxMiwwIEwxNCwwIFoiIGlkPSJDb21iaW5lZC1TaGFwZSIgZmlsbC1vcGFjaXR5PSIwLjMyIiBmaWxsPSIjMDAwMDAwIj48L3BhdGg+CiAgICAgICAgPC9nPgogICAgPC9nPgo8L3N2Zz4=', // wit
                '255255255l': 'PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0iVVRGLTgiPz4KPHN2ZyB3aWR0aD0iMTVweCIgaGVpZ2h0PSIxNXB4IiB2aWV3Qm94PSIwIDAgMTUgMTUiIHZlcnNpb249IjEuMSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIiB4bWxuczp4bGluaz0iaHR0cDovL3d3dy53My5vcmcvMTk5OS94bGluayI+CiAgICA8dGl0bGU+R3JvdXAgOCBDb3B5PC90aXRsZT4KICAgIDxnIGlkPSJGYXNlLTItdmVydm9sZyIgc3Ryb2tlPSJub25lIiBzdHJva2Utd2lkdGg9IjEiIGZpbGw9Im5vbmUiIGZpbGwtcnVsZT0iZXZlbm9kZCI+CiAgICAgICAgPGcgaWQ9Ikdyb3VwLTgtQ29weSI+CiAgICAgICAgICAgIDxwb2x5Z29uIGlkPSJNYXNrLUNvcHkiIGZpbGw9IiNGRkZGRkYiIHBvaW50cz0iLTguMDY2NDY0MTZlLTE0IDAgMTUgMCAxNSAxNSAwIDE1Ij48L3BvbHlnb24+CiAgICAgICAgICAgIDxwYXRoIGQ9Ik00LDAgTDQsMTUgTDIsMTUgTDIsMCBMNCwwIFogTTksMCBMOSwxNSBMNywxNSBMNywwIEw5LDAgWiBNMTQsMCBMMTQsMTUgTDEyLDE1IEwxMiwwIEwxNCwwIFoiIGlkPSJDb21iaW5lZC1TaGFwZSIgZmlsbC1vcGFjaXR5PSIwLjMyIiBmaWxsPSIjMDAwMDAwIj48L3BhdGg+CiAgICAgICAgPC9nPgogICAgPC9nPgo8L3N2Zz4=',
                '14811311': 'PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0iVVRGLTgiPz4KPHN2ZyB3aWR0aD0iMTVweCIgaGVpZ2h0PSIxNXB4IiB2aWV3Qm94PSIwIDAgMTUgMTUiIHZlcnNpb249IjEuMSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIiB4bWxuczp4bGluaz0iaHR0cDovL3d3dy53My5vcmcvMTk5OS94bGluayI+CiAgICA8dGl0bGU+R3JvdXAgODwvdGl0bGU+CiAgICA8ZyBpZD0iRmFzZS0yLXZlcnZvbGciIHN0cm9rZT0ibm9uZSIgc3Ryb2tlLXdpZHRoPSIxIiBmaWxsPSJub25lIiBmaWxsLXJ1bGU9ImV2ZW5vZGQiPgogICAgICAgIDxnIGlkPSJHcm91cC04Ij4KICAgICAgICAgICAgPHBvbHlnb24gaWQ9Ik1hc2siIGZpbGw9IiM5NDcxMEIiIHBvaW50cz0iLTguMDY2NDY0MTZlLTE0IDAgMTUgMCAxNSAxNSAwIDE1Ij48L3BvbHlnb24+CiAgICAgICAgICAgIDxwYXRoIGQ9Ik00LDAgTDQsMTUgTDIsMTUgTDIsMCBMNCwwIFogTTksMCBMOSwxNSBMNywxNSBMNywwIEw5LDAgWiBNMTQsMCBMMTQsMTUgTDEyLDE1IEwxMiwwIEwxNCwwIFoiIGlkPSJDb21iaW5lZC1TaGFwZSIgZmlsbC1vcGFjaXR5PSIwLjMyIiBmaWxsPSIjMDAwMDAwIj48L3BhdGg+CiAgICAgICAgPC9nPgogICAgPC9nPgo8L3N2Zz4=', // goud
                '14811311l': 'PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0iVVRGLTgiPz4KPHN2ZyB3aWR0aD0iMTVweCIgaGVpZ2h0PSIxNXB4IiB2aWV3Qm94PSIwIDAgMTUgMTUiIHZlcnNpb249IjEuMSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIiB4bWxuczp4bGluaz0iaHR0cDovL3d3dy53My5vcmcvMTk5OS94bGluayI+CiAgICA8dGl0bGU+R3JvdXAgODwvdGl0bGU+CiAgICA8ZyBpZD0iRmFzZS0yLXZlcnZvbGciIHN0cm9rZT0ibm9uZSIgc3Ryb2tlLXdpZHRoPSIxIiBmaWxsPSJub25lIiBmaWxsLXJ1bGU9ImV2ZW5vZGQiPgogICAgICAgIDxnIGlkPSJHcm91cC04Ij4KICAgICAgICAgICAgPHBvbHlnb24gaWQ9Ik1hc2siIGZpbGw9IiNDNkI1ODIiIHBvaW50cz0iLTguMDY2NDY0MTZlLTE0IDAgMTUgMCAxNSAxNSAwIDE1Ij48L3BvbHlnb24+CiAgICAgICAgICAgIDxwYXRoIGQ9Ik00LDAgTDQsMTUgTDIsMTUgTDIsMCBMNCwwIFogTTksMCBMOSwxNSBMNywxNSBMNywwIEw5LDAgWiBNMTQsMCBMMTQsMTUgTDEyLDE1IEwxMiwwIEwxNCwwIFoiIGlkPSJDb21iaW5lZC1TaGFwZSIgZmlsbC1vcGFjaXR5PSIwLjIiIGZpbGw9IiMwMDAwMDAiPjwvcGF0aD4KICAgICAgICA8L2c+CiAgICA8L2c+Cjwvc3ZnPg==',
                '2134330': 'PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0iVVRGLTgiPz4KPHN2ZyB3aWR0aD0iMTVweCIgaGVpZ2h0PSIxNXB4IiB2aWV3Qm94PSIwIDAgMTUgMTUiIHZlcnNpb249IjEuMSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIiB4bWxuczp4bGluaz0iaHR0cDovL3d3dy53My5vcmcvMTk5OS94bGluayI+CiAgICA8dGl0bGU+R3JvdXA8L3RpdGxlPgogICAgPGcgaWQ9IlBhZ2UtMSIgc3Ryb2tlPSJub25lIiBzdHJva2Utd2lkdGg9IjEiIGZpbGw9Im5vbmUiIGZpbGwtcnVsZT0iZXZlbm9kZCI+CiAgICAgICAgPGcgaWQ9Ikdyb3VwIj4KICAgICAgICAgICAgPHJlY3QgaWQ9IlJlY3RhbmdsZSIgZmlsbD0iI0Q1MkIxRSIgeD0iMCIgeT0iMCIgd2lkdGg9IjE1IiBoZWlnaHQ9IjE1Ij48L3JlY3Q+CiAgICAgICAgICAgIDxyZWN0IGlkPSJSZWN0YW5nbGUiIGZpbGwtb3BhY2l0eT0iMC4yIiBmaWxsPSIjMDAwMDAwIiB4PSIyIiB5PSIwIiB3aWR0aD0iMiIgaGVpZ2h0PSIxNSI+PC9yZWN0PgogICAgICAgICAgICA8cmVjdCBpZD0iUmVjdGFuZ2xlIiBmaWxsLW9wYWNpdHk9IjAuMiIgZmlsbD0iIzAwMDAwMCIgeD0iNyIgeT0iMCIgd2lkdGg9IjIiIGhlaWdodD0iMTUiPjwvcmVjdD4KICAgICAgICAgICAgPHJlY3QgaWQ9IlJlY3RhbmdsZSIgZmlsbC1vcGFjaXR5PSIwLjIiIGZpbGw9IiMwMDAwMDAiIHg9IjEyIiB5PSIwIiB3aWR0aD0iMiIgaGVpZ2h0PSIxNSI+PC9yZWN0PgogICAgICAgIDwvZz4KICAgIDwvZz4KPC9zdmc+', // rood
                '2134330l': 'PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0iVVRGLTgiPz4KPHN2ZyB3aWR0aD0iMTVweCIgaGVpZ2h0PSIxNXB4IiB2aWV3Qm94PSIwIDAgMTUgMTUiIHZlcnNpb249IjEuMSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIiB4bWxuczp4bGluaz0iaHR0cDovL3d3dy53My5vcmcvMTk5OS94bGluayI+CiAgICA8dGl0bGU+R3JvdXA8L3RpdGxlPgogICAgPGcgaWQ9IlBhZ2UtMSIgc3Ryb2tlPSJub25lIiBzdHJva2Utd2lkdGg9IjEiIGZpbGw9Im5vbmUiIGZpbGwtcnVsZT0iZXZlbm9kZCI+CiAgICAgICAgPGcgaWQ9Ikdyb3VwIj4KICAgICAgICAgICAgPHJlY3QgaWQ9IlJlY3RhbmdsZSIgZmlsbD0iI0YyQkZCQiIgeD0iMCIgeT0iMCIgd2lkdGg9IjE1IiBoZWlnaHQ9IjE1Ij48L3JlY3Q+CiAgICAgICAgICAgIDxyZWN0IGlkPSJSZWN0YW5nbGUiIGZpbGwtb3BhY2l0eT0iMC4xIiBmaWxsPSIjMDAwMDAwIiB4PSIyIiB5PSIwIiB3aWR0aD0iMiIgaGVpZ2h0PSIxNSI+PC9yZWN0PgogICAgICAgICAgICA8cmVjdCBpZD0iUmVjdGFuZ2xlIiBmaWxsLW9wYWNpdHk9IjAuMSIgZmlsbD0iIzAwMDAwMCIgeD0iNyIgeT0iMCIgd2lkdGg9IjIiIGhlaWdodD0iMTUiPjwvcmVjdD4KICAgICAgICAgICAgPHJlY3QgaWQ9IlJlY3RhbmdsZSIgZmlsbC1vcGFjaXR5PSIwLjEiIGZpbGw9IiMwMDAwMDAiIHg9IjEyIiB5PSIwIiB3aWR0aD0iMiIgaGVpZ2h0PSIxNSI+PC9yZWN0PgogICAgICAgIDwvZz4KICAgIDwvZz4KPC9zdmc+',
                '0123199': 'PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0iVVRGLTgiPz4KPHN2ZyB3aWR0aD0iMTVweCIgaGVpZ2h0PSIxNXB4IiB2aWV3Qm94PSIwIDAgMTUgMTUiIHZlcnNpb249IjEuMSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIiB4bWxuczp4bGluaz0iaHR0cDovL3d3dy53My5vcmcvMTk5OS94bGluayI+CiAgICA8dGl0bGU+R3JvdXA8L3RpdGxlPgogICAgPGcgaWQ9IlBhZ2UtMSIgc3Ryb2tlPSJub25lIiBzdHJva2Utd2lkdGg9IjEiIGZpbGw9Im5vbmUiIGZpbGwtcnVsZT0iZXZlbm9kZCI+CiAgICAgICAgPGcgaWQ9Ikdyb3VwIj4KICAgICAgICAgICAgPHJlY3QgaWQ9IlJlY3RhbmdsZSIgZmlsbD0iIzAwN0JDNyIgeD0iMCIgeT0iMCIgd2lkdGg9IjE1IiBoZWlnaHQ9IjE1Ij48L3JlY3Q+CiAgICAgICAgICAgIDxyZWN0IGlkPSJSZWN0YW5nbGUiIGZpbGwtb3BhY2l0eT0iMC4zIiBmaWxsPSIjRkZGRkZGIiB4PSIyIiB5PSIwIiB3aWR0aD0iMiIgaGVpZ2h0PSIxNSI+PC9yZWN0PgogICAgICAgICAgICA8cmVjdCBpZD0iUmVjdGFuZ2xlIiBmaWxsLW9wYWNpdHk9IjAuMyIgZmlsbD0iI0ZGRkZGRiIgeD0iNyIgeT0iMCIgd2lkdGg9IjIiIGhlaWdodD0iMTUiPjwvcmVjdD4KICAgICAgICAgICAgPHJlY3QgaWQ9IlJlY3RhbmdsZSIgZmlsbC1vcGFjaXR5PSIwLjMiIGZpbGw9IiNGRkZGRkYiIHg9IjEyIiB5PSIwIiB3aWR0aD0iMiIgaGVpZ2h0PSIxNSI+PC9yZWN0PgogICAgICAgIDwvZz4KICAgIDwvZz4KPC9zdmc+', // blauw
                '0123199l': 'PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0iVVRGLTgiPz4KPHN2ZyB3aWR0aD0iMTVweCIgaGVpZ2h0PSIxNXB4IiB2aWV3Qm94PSIwIDAgMTUgMTUiIHZlcnNpb249IjEuMSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIiB4bWxuczp4bGluaz0iaHR0cDovL3d3dy53My5vcmcvMTk5OS94bGluayI+CiAgICA8dGl0bGU+R3JvdXA8L3RpdGxlPgogICAgPGcgaWQ9IlBhZ2UtMSIgc3Ryb2tlPSJub25lIiBzdHJva2Utd2lkdGg9IjEiIGZpbGw9Im5vbmUiIGZpbGwtcnVsZT0iZXZlbm9kZCI+CiAgICAgICAgPGcgaWQ9Ikdyb3VwIj4KICAgICAgICAgICAgPHJlY3QgaWQ9IlJlY3RhbmdsZSIgZmlsbD0iI0IyRDFFMSIgeD0iMCIgeT0iMCIgd2lkdGg9IjE1IiBoZWlnaHQ9IjE1Ij48L3JlY3Q+CiAgICAgICAgICAgIDxyZWN0IGlkPSJSZWN0YW5nbGUiIGZpbGwtb3BhY2l0eT0iMC4xIiBmaWxsPSIjMDAwMDAwIiB4PSIyIiB5PSIwIiB3aWR0aD0iMiIgaGVpZ2h0PSIxNSI+PC9yZWN0PgogICAgICAgICAgICA8cmVjdCBpZD0iUmVjdGFuZ2xlIiBmaWxsLW9wYWNpdHk9IjAuMSIgZmlsbD0iIzAwMDAwMCIgeD0iNyIgeT0iMCIgd2lkdGg9IjIiIGhlaWdodD0iMTUiPjwvcmVjdD4KICAgICAgICAgICAgPHJlY3QgaWQ9IlJlY3RhbmdsZSIgZmlsbC1vcGFjaXR5PSIwLjEiIGZpbGw9IiMwMDAwMDAiIHg9IjEyIiB5PSIwIiB3aWR0aD0iMiIgaGVpZ2h0PSIxNSI+PC9yZWN0PgogICAgICAgIDwvZz4KICAgIDwvZz4KPC9zdmc+',
                '118210182': 'PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0iVVRGLTgiPz4KPHN2ZyB3aWR0aD0iMTVweCIgaGVpZ2h0PSIxNXB4IiB2aWV3Qm94PSIwIDAgMTUgMTUiIHZlcnNpb249IjEuMSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIiB4bWxuczp4bGluaz0iaHR0cDovL3d3dy53My5vcmcvMTk5OS94bGluayI+CiAgICA8dGl0bGU+R3JvdXA8L3RpdGxlPgogICAgPGcgaWQ9IlBhZ2UtMSIgc3Ryb2tlPSJub25lIiBzdHJva2Utd2lkdGg9IjEiIGZpbGw9Im5vbmUiIGZpbGwtcnVsZT0iZXZlbm9kZCI+CiAgICAgICAgPGcgaWQ9Ikdyb3VwIj4KICAgICAgICAgICAgPHJlY3QgaWQ9IlJlY3RhbmdsZSIgZmlsbD0iIzc2RDJCNiIgeD0iMCIgeT0iMCIgd2lkdGg9IjE1IiBoZWlnaHQ9IjE1Ij48L3JlY3Q+CiAgICAgICAgICAgIDxyZWN0IGlkPSJSZWN0YW5nbGUiIGZpbGwtb3BhY2l0eT0iMC4yIiBmaWxsPSIjMDAwMDAwIiB4PSIyIiB5PSIwIiB3aWR0aD0iMiIgaGVpZ2h0PSIxNSI+PC9yZWN0PgogICAgICAgICAgICA8cmVjdCBpZD0iUmVjdGFuZ2xlIiBmaWxsLW9wYWNpdHk9IjAuMiIgZmlsbD0iIzAwMDAwMCIgeD0iNyIgeT0iMCIgd2lkdGg9IjIiIGhlaWdodD0iMTUiPjwvcmVjdD4KICAgICAgICAgICAgPHJlY3QgaWQ9IlJlY3RhbmdsZSIgZmlsbC1vcGFjaXR5PSIwLjIiIGZpbGw9IiMwMDAwMDAiIHg9IjEyIiB5PSIwIiB3aWR0aD0iMiIgaGVpZ2h0PSIxNSI+PC9yZWN0PgogICAgICAgIDwvZz4KICAgIDwvZz4KPC9zdmc+', // mint
                '118210182l': 'PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0iVVRGLTgiPz4KPHN2ZyB3aWR0aD0iMTVweCIgaGVpZ2h0PSIxNXB4IiB2aWV3Qm94PSIwIDAgMTUgMTUiIHZlcnNpb249IjEuMSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIiB4bWxuczp4bGluaz0iaHR0cDovL3d3dy53My5vcmcvMTk5OS94bGluayI+CiAgICA8dGl0bGU+R3JvdXA8L3RpdGxlPgogICAgPGcgaWQ9IlBhZ2UtMSIgc3Ryb2tlPSJub25lIiBzdHJva2Utd2lkdGg9IjEiIGZpbGw9Im5vbmUiIGZpbGwtcnVsZT0iZXZlbm9kZCI+CiAgICAgICAgPGcgaWQ9Ikdyb3VwIj4KICAgICAgICAgICAgPHJlY3QgaWQ9IlJlY3RhbmdsZSIgZmlsbD0iI0Q1RjFFOSIgeD0iMCIgeT0iMCIgd2lkdGg9IjE1IiBoZWlnaHQ9IjE1Ij48L3JlY3Q+CiAgICAgICAgICAgIDxyZWN0IGlkPSJSZWN0YW5nbGUiIGZpbGwtb3BhY2l0eT0iMC4xIiBmaWxsPSIjMDAwMDAwIiB4PSIyIiB5PSIwIiB3aWR0aD0iMiIgaGVpZ2h0PSIxNSI+PC9yZWN0PgogICAgICAgICAgICA8cmVjdCBpZD0iUmVjdGFuZ2xlIiBmaWxsLW9wYWNpdHk9IjAuMSIgZmlsbD0iIzAwMDAwMCIgeD0iNyIgeT0iMCIgd2lkdGg9IjIiIGhlaWdodD0iMTUiPjwvcmVjdD4KICAgICAgICAgICAgPHJlY3QgaWQ9IlJlY3RhbmdsZSIgZmlsbC1vcGFjaXR5PSIwLjEiIGZpbGw9IiMwMDAwMDAiIHg9IjEyIiB5PSIwIiB3aWR0aD0iMiIgaGVpZ2h0PSIxNSI+PC9yZWN0PgogICAgICAgIDwvZz4KICAgIDwvZz4KPC9zdmc+',
                '2251120': 'PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0iVVRGLTgiPz4KPHN2ZyB3aWR0aD0iMTVweCIgaGVpZ2h0PSIxNXB4IiB2aWV3Qm94PSIwIDAgMTUgMTUiIHZlcnNpb249IjEuMSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIiB4bWxuczp4bGluaz0iaHR0cDovL3d3dy53My5vcmcvMTk5OS94bGluayI+CiAgICA8dGl0bGU+R3JvdXA8L3RpdGxlPgogICAgPGcgaWQ9IlBhZ2UtMSIgc3Ryb2tlPSJub25lIiBzdHJva2Utd2lkdGg9IjEiIGZpbGw9Im5vbmUiIGZpbGwtcnVsZT0iZXZlbm9kZCI+CiAgICAgICAgPGcgaWQ9Ikdyb3VwIj4KICAgICAgICAgICAgPHJlY3QgaWQ9IlJlY3RhbmdsZSIgZmlsbD0iI0UxNzAwMCIgeD0iMCIgeT0iMCIgd2lkdGg9IjE1IiBoZWlnaHQ9IjE1Ij48L3JlY3Q+CiAgICAgICAgICAgIDxyZWN0IGlkPSJSZWN0YW5nbGUiIGZpbGwtb3BhY2l0eT0iMC4yIiBmaWxsPSIjMDAwMDAwIiB4PSIyIiB5PSIwIiB3aWR0aD0iMiIgaGVpZ2h0PSIxNSI+PC9yZWN0PgogICAgICAgICAgICA8cmVjdCBpZD0iUmVjdGFuZ2xlIiBmaWxsLW9wYWNpdHk9IjAuMiIgZmlsbD0iIzAwMDAwMCIgeD0iNyIgeT0iMCIgd2lkdGg9IjIiIGhlaWdodD0iMTUiPjwvcmVjdD4KICAgICAgICAgICAgPHJlY3QgaWQ9IlJlY3RhbmdsZSIgZmlsbC1vcGFjaXR5PSIwLjIiIGZpbGw9IiMwMDAwMDAiIHg9IjEyIiB5PSIwIiB3aWR0aD0iMiIgaGVpZ2h0PSIxNSI+PC9yZWN0PgogICAgICAgIDwvZz4KICAgIDwvZz4KPC9zdmc+', // orange
                '2251120l': 'PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0iVVRGLTgiPz4KPHN2ZyB3aWR0aD0iMTVweCIgaGVpZ2h0PSIxNXB4IiB2aWV3Qm94PSIwIDAgMTUgMTUiIHZlcnNpb249IjEuMSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIiB4bWxuczp4bGluaz0iaHR0cDovL3d3dy53My5vcmcvMTk5OS94bGluayI+CiAgICA8dGl0bGU+R3JvdXA8L3RpdGxlPgogICAgPGcgaWQ9IlBhZ2UtMSIgc3Ryb2tlPSJub25lIiBzdHJva2Utd2lkdGg9IjEiIGZpbGw9Im5vbmUiIGZpbGwtcnVsZT0iZXZlbm9kZCI+CiAgICAgICAgPGcgaWQ9Ikdyb3VwIj4KICAgICAgICAgICAgPHJlY3QgaWQ9IlJlY3RhbmdsZSIgZmlsbD0iI0Y2RDRCMiIgeD0iMCIgeT0iMCIgd2lkdGg9IjE1IiBoZWlnaHQ9IjE1Ij48L3JlY3Q+CiAgICAgICAgICAgIDxyZWN0IGlkPSJSZWN0YW5nbGUiIGZpbGwtb3BhY2l0eT0iMC4xIiBmaWxsPSIjMDAwMDAwIiB4PSIyIiB5PSIwIiB3aWR0aD0iMiIgaGVpZ2h0PSIxNSI+PC9yZWN0PgogICAgICAgICAgICA8cmVjdCBpZD0iUmVjdGFuZ2xlIiBmaWxsLW9wYWNpdHk9IjAuMSIgZmlsbD0iIzAwMDAwMCIgeD0iNyIgeT0iMCIgd2lkdGg9IjIiIGhlaWdodD0iMTUiPjwvcmVjdD4KICAgICAgICAgICAgPHJlY3QgaWQ9IlJlY3RhbmdsZSIgZmlsbC1vcGFjaXR5PSIwLjEiIGZpbGw9IiMwMDAwMDAiIHg9IjEyIiB5PSIwIiB3aWR0aD0iMiIgaGVpZ2h0PSIxNSI+PC9yZWN0PgogICAgICAgIDwvZz4KICAgIDwvZz4KPC9zdmc+',
                '25518218': 'PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0iVVRGLTgiPz4KPHN2ZyB3aWR0aD0iMTVweCIgaGVpZ2h0PSIxNXB4IiB2aWV3Qm94PSIwIDAgMTUgMTUiIHZlcnNpb249IjEuMSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIiB4bWxuczp4bGluaz0iaHR0cDovL3d3dy53My5vcmcvMTk5OS94bGluayI+CiAgICA8dGl0bGU+R3JvdXA8L3RpdGxlPgogICAgPGcgaWQ9IlBhZ2UtMSIgc3Ryb2tlPSJub25lIiBzdHJva2Utd2lkdGg9IjEiIGZpbGw9Im5vbmUiIGZpbGwtcnVsZT0iZXZlbm9kZCI+CiAgICAgICAgPGcgaWQ9Ikdyb3VwIj4KICAgICAgICAgICAgPHJlY3QgaWQ9IlJlY3RhbmdsZSIgZmlsbD0iI0ZGQjYxMiIgeD0iMCIgeT0iMCIgd2lkdGg9IjE1IiBoZWlnaHQ9IjE1Ij48L3JlY3Q+CiAgICAgICAgICAgIDxyZWN0IGlkPSJSZWN0YW5nbGUiIGZpbGwtb3BhY2l0eT0iMC4yIiBmaWxsPSIjMDAwMDAwIiB4PSIyIiB5PSIwIiB3aWR0aD0iMiIgaGVpZ2h0PSIxNSI+PC9yZWN0PgogICAgICAgICAgICA8cmVjdCBpZD0iUmVjdGFuZ2xlIiBmaWxsLW9wYWNpdHk9IjAuMiIgZmlsbD0iIzAwMDAwMCIgeD0iNyIgeT0iMCIgd2lkdGg9IjIiIGhlaWdodD0iMTUiPjwvcmVjdD4KICAgICAgICAgICAgPHJlY3QgaWQ9IlJlY3RhbmdsZSIgZmlsbC1vcGFjaXR5PSIwLjIiIGZpbGw9IiMwMDAwMDAiIHg9IjEyIiB5PSIwIiB3aWR0aD0iMiIgaGVpZ2h0PSIxNSI+PC9yZWN0PgogICAgICAgIDwvZz4KICAgIDwvZz4KPC9zdmc+', // yellow
                '25518218l': 'PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0iVVRGLTgiPz4KPHN2ZyB3aWR0aD0iMTVweCIgaGVpZ2h0PSIxNXB4IiB2aWV3Qm94PSIwIDAgMTUgMTUiIHZlcnNpb249IjEuMSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIiB4bWxuczp4bGluaz0iaHR0cDovL3d3dy53My5vcmcvMTk5OS94bGluayI+CiAgICA8dGl0bGU+R3JvdXA8L3RpdGxlPgogICAgPGcgaWQ9IlBhZ2UtMSIgc3Ryb2tlPSJub25lIiBzdHJva2Utd2lkdGg9IjEiIGZpbGw9Im5vbmUiIGZpbGwtcnVsZT0iZXZlbm9kZCI+CiAgICAgICAgPGcgaWQ9Ikdyb3VwIj4KICAgICAgICAgICAgPHJlY3QgaWQ9IlJlY3RhbmdsZSIgZmlsbD0iI0ZGRTlCNyIgeD0iMCIgeT0iMCIgd2lkdGg9IjE1IiBoZWlnaHQ9IjE1Ij48L3JlY3Q+CiAgICAgICAgICAgIDxyZWN0IGlkPSJSZWN0YW5nbGUiIGZpbGwtb3BhY2l0eT0iMC4xIiBmaWxsPSIjMDAwMDAwIiB4PSIyIiB5PSIwIiB3aWR0aD0iMiIgaGVpZ2h0PSIxNSI+PC9yZWN0PgogICAgICAgICAgICA8cmVjdCBpZD0iUmVjdGFuZ2xlIiBmaWxsLW9wYWNpdHk9IjAuMSIgZmlsbD0iIzAwMDAwMCIgeD0iNyIgeT0iMCIgd2lkdGg9IjIiIGhlaWdodD0iMTUiPjwvcmVjdD4KICAgICAgICAgICAgPHJlY3QgaWQ9IlJlY3RhbmdsZSIgZmlsbC1vcGFjaXR5PSIwLjEiIGZpbGw9IiMwMDAwMDAiIHg9IjEyIiB5PSIwIiB3aWR0aD0iMiIgaGVpZ2h0PSIxNSI+PC9yZWN0PgogICAgICAgIDwvZz4KICAgIDwvZz4KPC9zdmc+',
                '24922530': 'PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0iVVRGLTgiPz4KPHN2ZyB3aWR0aD0iMTVweCIgaGVpZ2h0PSIxNXB4IiB2aWV3Qm94PSIwIDAgMTUgMTUiIHZlcnNpb249IjEuMSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIiB4bWxuczp4bGluaz0iaHR0cDovL3d3dy53My5vcmcvMTk5OS94bGluayI+CiAgICA8dGl0bGU+R3JvdXA8L3RpdGxlPgogICAgPGcgaWQ9IlBhZ2UtMSIgc3Ryb2tlPSJub25lIiBzdHJva2Utd2lkdGg9IjEiIGZpbGw9Im5vbmUiIGZpbGwtcnVsZT0iZXZlbm9kZCI+CiAgICAgICAgPGcgaWQ9Ikdyb3VwIj4KICAgICAgICAgICAgPHJlY3QgaWQ9IlJlY3RhbmdsZSIgZmlsbD0iI0Y5RTExRSIgeD0iMCIgeT0iMCIgd2lkdGg9IjE1IiBoZWlnaHQ9IjE1Ij48L3JlY3Q+CiAgICAgICAgICAgIDxyZWN0IGlkPSJSZWN0YW5nbGUiIGZpbGwtb3BhY2l0eT0iMC4xIiBmaWxsPSIjMDAwMDAwIiB4PSIyIiB5PSIwIiB3aWR0aD0iMiIgaGVpZ2h0PSIxNSI+PC9yZWN0PgogICAgICAgICAgICA8cmVjdCBpZD0iUmVjdGFuZ2xlIiBmaWxsLW9wYWNpdHk9IjAuMSIgZmlsbD0iIzAwMDAwMCIgeD0iNyIgeT0iMCIgd2lkdGg9IjIiIGhlaWdodD0iMTUiPjwvcmVjdD4KICAgICAgICAgICAgPHJlY3QgaWQ9IlJlY3RhbmdsZSIgZmlsbC1vcGFjaXR5PSIwLjEiIGZpbGw9IiMwMDAwMDAiIHg9IjEyIiB5PSIwIiB3aWR0aD0iMiIgaGVpZ2h0PSIxNSI+PC9yZWN0PgogICAgICAgIDwvZz4KICAgIDwvZz4KPC9zdmc+', // bright yellow
                '24922530l': 'PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0iVVRGLTgiPz4KPHN2ZyB3aWR0aD0iMTVweCIgaGVpZ2h0PSIxNXB4IiB2aWV3Qm94PSIwIDAgMTUgMTUiIHZlcnNpb249IjEuMSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIiB4bWxuczp4bGluaz0iaHR0cDovL3d3dy53My5vcmcvMTk5OS94bGluayI+CiAgICA8dGl0bGU+R3JvdXA8L3RpdGxlPgogICAgPGcgaWQ9IlBhZ2UtMSIgc3Ryb2tlPSJub25lIiBzdHJva2Utd2lkdGg9IjEiIGZpbGw9Im5vbmUiIGZpbGwtcnVsZT0iZXZlbm9kZCI+CiAgICAgICAgPGcgaWQ9Ikdyb3VwIj4KICAgICAgICAgICAgPHJlY3QgaWQ9IlJlY3RhbmdsZSIgZmlsbD0iI0ZERjZCQiIgeD0iMCIgeT0iMCIgd2lkdGg9IjE1IiBoZWlnaHQ9IjE1Ij48L3JlY3Q+CiAgICAgICAgICAgIDxyZWN0IGlkPSJSZWN0YW5nbGUiIGZpbGwtb3BhY2l0eT0iMC4xIiBmaWxsPSIjMDAwMDAwIiB4PSIyIiB5PSIwIiB3aWR0aD0iMiIgaGVpZ2h0PSIxNSI+PC9yZWN0PgogICAgICAgICAgICA8cmVjdCBpZD0iUmVjdGFuZ2xlIiBmaWxsLW9wYWNpdHk9IjAuMSIgZmlsbD0iIzAwMDAwMCIgeD0iNyIgeT0iMCIgd2lkdGg9IjIiIGhlaWdodD0iMTUiPjwvcmVjdD4KICAgICAgICAgICAgPHJlY3QgaWQ9IlJlY3RhbmdsZSIgZmlsbC1vcGFjaXR5PSIwLjEiIGZpbGw9IiMwMDAwMDAiIHg9IjEyIiB5PSIwIiB3aWR0aD0iMiIgaGVpZ2h0PSIxNSI+PC9yZWN0PgogICAgICAgIDwvZz4KICAgIDwvZz4KPC9zdmc+',
                '1104155': 'PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0iVVRGLTgiPz4KPHN2ZyB3aWR0aD0iMTVweCIgaGVpZ2h0PSIxNXB4IiB2aWV3Qm94PSIwIDAgMTUgMTUiIHZlcnNpb249IjEuMSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIiB4bWxuczp4bGluaz0iaHR0cDovL3d3dy53My5vcmcvMTk5OS94bGluayI+CiAgICA8dGl0bGU+R3JvdXA8L3RpdGxlPgogICAgPGcgaWQ9IlBhZ2UtMSIgc3Ryb2tlPSJub25lIiBzdHJva2Utd2lkdGg9IjEiIGZpbGw9Im5vbmUiIGZpbGwtcnVsZT0iZXZlbm9kZCI+CiAgICAgICAgPGcgaWQ9Ikdyb3VwIj4KICAgICAgICAgICAgPHJlY3QgaWQ9IlJlY3RhbmdsZSIgZmlsbD0iIzAxNjg5QiIgeD0iMCIgeT0iMCIgd2lkdGg9IjE1IiBoZWlnaHQ9IjE1Ij48L3JlY3Q+CiAgICAgICAgICAgIDxyZWN0IGlkPSJSZWN0YW5nbGUiIGZpbGwtb3BhY2l0eT0iMC4zIiBmaWxsPSIjRkZGRkZGIiB4PSIyIiB5PSIwIiB3aWR0aD0iMiIgaGVpZ2h0PSIxNSI+PC9yZWN0PgogICAgICAgICAgICA8cmVjdCBpZD0iUmVjdGFuZ2xlIiBmaWxsLW9wYWNpdHk9IjAuMyIgZmlsbD0iI0ZGRkZGRiIgeD0iNyIgeT0iMCIgd2lkdGg9IjIiIGhlaWdodD0iMTUiPjwvcmVjdD4KICAgICAgICAgICAgPHJlY3QgaWQ9IlJlY3RhbmdsZSIgZmlsbC1vcGFjaXR5PSIwLjMiIGZpbGw9IiNGRkZGRkYiIHg9IjEyIiB5PSIwIiB3aWR0aD0iMiIgaGVpZ2h0PSIxNSI+PC9yZWN0PgogICAgICAgIDwvZz4KICAgIDwvZz4KPC9zdmc+', // darkblue
                '1104155l': 'PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0iVVRGLTgiPz4KPHN2ZyB3aWR0aD0iMTVweCIgaGVpZ2h0PSIxNXB4IiB2aWV3Qm94PSIwIDAgMTUgMTUiIHZlcnNpb249IjEuMSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIiB4bWxuczp4bGluaz0iaHR0cDovL3d3dy53My5vcmcvMTk5OS94bGluayI+CiAgICA8dGl0bGU+R3JvdXA8L3RpdGxlPgogICAgPGcgaWQ9IlBhZ2UtMSIgc3Ryb2tlPSJub25lIiBzdHJva2Utd2lkdGg9IjEiIGZpbGw9Im5vbmUiIGZpbGwtcnVsZT0iZXZlbm9kZCI+CiAgICAgICAgPGcgaWQ9Ikdyb3VwIj4KICAgICAgICAgICAgPHJlY3QgaWQ9IlJlY3RhbmdsZSIgZmlsbD0iI0IyRDFFMSIgeD0iMCIgeT0iMCIgd2lkdGg9IjE1IiBoZWlnaHQ9IjE1Ij48L3JlY3Q+CiAgICAgICAgICAgIDxyZWN0IGlkPSJSZWN0YW5nbGUiIGZpbGwtb3BhY2l0eT0iMC4xIiBmaWxsPSIjMDAwMDAwIiB4PSIyIiB5PSIwIiB3aWR0aD0iMiIgaGVpZ2h0PSIxNSI+PC9yZWN0PgogICAgICAgICAgICA8cmVjdCBpZD0iUmVjdGFuZ2xlIiBmaWxsLW9wYWNpdHk9IjAuMSIgZmlsbD0iIzAwMDAwMCIgeD0iNyIgeT0iMCIgd2lkdGg9IjIiIGhlaWdodD0iMTUiPjwvcmVjdD4KICAgICAgICAgICAgPHJlY3QgaWQ9IlJlY3RhbmdsZSIgZmlsbC1vcGFjaXR5PSIwLjEiIGZpbGw9IiMwMDAwMDAiIHg9IjEyIiB5PSIwIiB3aWR0aD0iMiIgaGVpZ2h0PSIxNSI+PC9yZWN0PgogICAgICAgIDwvZz4KICAgIDwvZz4KPC9zdmc+',
                '11419025': 'PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0iVVRGLTgiPz4KPHN2ZyB3aWR0aD0iMTVweCIgaGVpZ2h0PSIxNXB4IiB2aWV3Qm94PSIwIDAgMTUgMTUiIHZlcnNpb249IjEuMSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIiB4bWxuczp4bGluaz0iaHR0cDovL3d3dy53My5vcmcvMTk5OS94bGluayI+CiAgICA8dGl0bGU+R3JvdXA8L3RpdGxlPgogICAgPGcgaWQ9IlBhZ2UtMSIgc3Ryb2tlPSJub25lIiBzdHJva2Utd2lkdGg9IjEiIGZpbGw9Im5vbmUiIGZpbGwtcnVsZT0iZXZlbm9kZCI+CiAgICAgICAgPGcgaWQ9Ikdyb3VwIj4KICAgICAgICAgICAgPHJlY3QgaWQ9IlJlY3RhbmdsZSIgZmlsbD0iIzcyQkUxOCIgeD0iMCIgeT0iMCIgd2lkdGg9IjE1IiBoZWlnaHQ9IjE1Ij48L3JlY3Q+CiAgICAgICAgICAgIDxyZWN0IGlkPSJSZWN0YW5nbGUiIGZpbGwtb3BhY2l0eT0iMC4yIiBmaWxsPSIjMDAwMDAwIiB4PSIyIiB5PSIwIiB3aWR0aD0iMiIgaGVpZ2h0PSIxNSI+PC9yZWN0PgogICAgICAgICAgICA8cmVjdCBpZD0iUmVjdGFuZ2xlIiBmaWxsLW9wYWNpdHk9IjAuMiIgZmlsbD0iIzAwMDAwMCIgeD0iNyIgeT0iMCIgd2lkdGg9IjIiIGhlaWdodD0iMTUiPjwvcmVjdD4KICAgICAgICAgICAgPHJlY3QgaWQ9IlJlY3RhbmdsZSIgZmlsbC1vcGFjaXR5PSIwLjIiIGZpbGw9IiMwMDAwMDAiIHg9IjEyIiB5PSIwIiB3aWR0aD0iMiIgaGVpZ2h0PSIxNSI+PC9yZWN0PgogICAgICAgIDwvZz4KICAgIDwvZz4KPC9zdmc+', // lightgreen
                '11419025l': 'PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0iVVRGLTgiPz4KPHN2ZyB3aWR0aD0iMTVweCIgaGVpZ2h0PSIxNXB4IiB2aWV3Qm94PSIwIDAgMTUgMTUiIHZlcnNpb249IjEuMSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIiB4bWxuczp4bGluaz0iaHR0cDovL3d3dy53My5vcmcvMTk5OS94bGluayI+CiAgICA8dGl0bGU+R3JvdXA8L3RpdGxlPgogICAgPGcgaWQ9IlBhZ2UtMSIgc3Ryb2tlPSJub25lIiBzdHJva2Utd2lkdGg9IjEiIGZpbGw9Im5vbmUiIGZpbGwtcnVsZT0iZXZlbm9kZCI+CiAgICAgICAgPGcgaWQ9Ikdyb3VwIj4KICAgICAgICAgICAgPHJlY3QgaWQ9IlJlY3RhbmdsZSIgZmlsbD0iI0Q0RUJCQSIgeD0iMCIgeT0iMCIgd2lkdGg9IjE1IiBoZWlnaHQ9IjE1Ij48L3JlY3Q+CiAgICAgICAgICAgIDxyZWN0IGlkPSJSZWN0YW5nbGUiIGZpbGwtb3BhY2l0eT0iMC4xIiBmaWxsPSIjMDAwMDAwIiB4PSIyIiB5PSIwIiB3aWR0aD0iMiIgaGVpZ2h0PSIxNSI+PC9yZWN0PgogICAgICAgICAgICA8cmVjdCBpZD0iUmVjdGFuZ2xlIiBmaWxsLW9wYWNpdHk9IjAuMSIgZmlsbD0iIzAwMDAwMCIgeD0iNyIgeT0iMCIgd2lkdGg9IjIiIGhlaWdodD0iMTUiPjwvcmVjdD4KICAgICAgICAgICAgPHJlY3QgaWQ9IlJlY3RhbmdsZSIgZmlsbC1vcGFjaXR5PSIwLjEiIGZpbGw9IiMwMDAwMDAiIHg9IjEyIiB5PSIwIiB3aWR0aD0iMiIgaGVpZ2h0PSIxNSI+PC9yZWN0PgogICAgICAgIDwvZz4KICAgIDwvZz4KPC9zdmc+',
                '5713512': 'PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0iVVRGLTgiPz4KPHN2ZyB3aWR0aD0iMTVweCIgaGVpZ2h0PSIxNXB4IiB2aWV3Qm94PSIwIDAgMTUgMTUiIHZlcnNpb249IjEuMSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIiB4bWxuczp4bGluaz0iaHR0cDovL3d3dy53My5vcmcvMTk5OS94bGluayI+CiAgICA8dGl0bGU+R3JvdXA8L3RpdGxlPgogICAgPGcgaWQ9IlBhZ2UtMSIgc3Ryb2tlPSJub25lIiBzdHJva2Utd2lkdGg9IjEiIGZpbGw9Im5vbmUiIGZpbGwtcnVsZT0iZXZlbm9kZCI+CiAgICAgICAgPGcgaWQ9Ikdyb3VwIj4KICAgICAgICAgICAgPHJlY3QgaWQ9IlJlY3RhbmdsZSIgZmlsbD0iIzM5ODcwQyIgeD0iMCIgeT0iMCIgd2lkdGg9IjE1IiBoZWlnaHQ9IjE1Ij48L3JlY3Q+CiAgICAgICAgICAgIDxyZWN0IGlkPSJSZWN0YW5nbGUiIGZpbGwtb3BhY2l0eT0iMC4zIiBmaWxsPSIjRkZGRkZGIiB4PSIyIiB5PSIwIiB3aWR0aD0iMiIgaGVpZ2h0PSIxNSI+PC9yZWN0PgogICAgICAgICAgICA8cmVjdCBpZD0iUmVjdGFuZ2xlIiBmaWxsLW9wYWNpdHk9IjAuMyIgZmlsbD0iI0ZGRkZGRiIgeD0iNyIgeT0iMCIgd2lkdGg9IjIiIGhlaWdodD0iMTUiPjwvcmVjdD4KICAgICAgICAgICAgPHJlY3QgaWQ9IlJlY3RhbmdsZSIgZmlsbC1vcGFjaXR5PSIwLjMiIGZpbGw9IiNGRkZGRkYiIHg9IjEyIiB5PSIwIiB3aWR0aD0iMiIgaGVpZ2h0PSIxNSI+PC9yZWN0PgogICAgICAgIDwvZz4KICAgIDwvZz4KPC9zdmc+', // darkgreen
                '5713512l': 'PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0iVVRGLTgiPz4KPHN2ZyB3aWR0aD0iMTVweCIgaGVpZ2h0PSIxNXB4IiB2aWV3Qm94PSIwIDAgMTUgMTUiIHZlcnNpb249IjEuMSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIiB4bWxuczp4bGluaz0iaHR0cDovL3d3dy53My5vcmcvMTk5OS94bGluayI+CiAgICA8dGl0bGU+R3JvdXA8L3RpdGxlPgogICAgPGcgaWQ9IlBhZ2UtMSIgc3Ryb2tlPSJub25lIiBzdHJva2Utd2lkdGg9IjEiIGZpbGw9Im5vbmUiIGZpbGwtcnVsZT0iZXZlbm9kZCI+CiAgICAgICAgPGcgaWQ9Ikdyb3VwIj4KICAgICAgICAgICAgPHJlY3QgaWQ9IlJlY3RhbmdsZSIgZmlsbD0iI0MzREJCNiIgeD0iMCIgeT0iMCIgd2lkdGg9IjE1IiBoZWlnaHQ9IjE1Ij48L3JlY3Q+CiAgICAgICAgICAgIDxyZWN0IGlkPSJSZWN0YW5nbGUiIGZpbGwtb3BhY2l0eT0iMC4zIiBmaWxsPSIjRkZGRkZGIiB4PSIyIiB5PSIwIiB3aWR0aD0iMiIgaGVpZ2h0PSIxNSI+PC9yZWN0PgogICAgICAgICAgICA8cmVjdCBpZD0iUmVjdGFuZ2xlIiBmaWxsLW9wYWNpdHk9IjAuMyIgZmlsbD0iI0ZGRkZGRiIgeD0iNyIgeT0iMCIgd2lkdGg9IjIiIGhlaWdodD0iMTUiPjwvcmVjdD4KICAgICAgICAgICAgPHJlY3QgaWQ9IlJlY3RhbmdsZSIgZmlsbC1vcGFjaXR5PSIwLjMiIGZpbGw9IiNGRkZGRkYiIHg9IjEyIiB5PSIwIiB3aWR0aD0iMiIgaGVpZ2h0PSIxNSI+PC9yZWN0PgogICAgICAgIDwvZz4KICAgIDwvZz4KPC9zdmc+',
                '662095': 'PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0iVVRGLTgiPz4KPHN2ZyB3aWR0aD0iMTVweCIgaGVpZ2h0PSIxNXB4IiB2aWV3Qm94PSIwIDAgMTUgMTUiIHZlcnNpb249IjEuMSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIiB4bWxuczp4bGluaz0iaHR0cDovL3d3dy53My5vcmcvMTk5OS94bGluayI+CiAgICA8dGl0bGU+R3JvdXA8L3RpdGxlPgogICAgPGcgaWQ9IlBhZ2UtMSIgc3Ryb2tlPSJub25lIiBzdHJva2Utd2lkdGg9IjEiIGZpbGw9Im5vbmUiIGZpbGwtcnVsZT0iZXZlbm9kZCI+CiAgICAgICAgPGcgaWQ9Ikdyb3VwIj4KICAgICAgICAgICAgPHJlY3QgaWQ9IlJlY3RhbmdsZSIgZmlsbD0iIzQyMTQ1RiIgeD0iMCIgeT0iMCIgd2lkdGg9IjE1IiBoZWlnaHQ9IjE1Ij48L3JlY3Q+CiAgICAgICAgICAgIDxyZWN0IGlkPSJSZWN0YW5nbGUiIGZpbGwtb3BhY2l0eT0iMC4zIiBmaWxsPSIjRkZGRkZGIiB4PSIyIiB5PSIwIiB3aWR0aD0iMiIgaGVpZ2h0PSIxNSI+PC9yZWN0PgogICAgICAgICAgICA8cmVjdCBpZD0iUmVjdGFuZ2xlIiBmaWxsLW9wYWNpdHk9IjAuMyIgZmlsbD0iI0ZGRkZGRiIgeD0iNyIgeT0iMCIgd2lkdGg9IjIiIGhlaWdodD0iMTUiPjwvcmVjdD4KICAgICAgICAgICAgPHJlY3QgaWQ9IlJlY3RhbmdsZSIgZmlsbC1vcGFjaXR5PSIwLjMiIGZpbGw9IiNGRkZGRkYiIHg9IjEyIiB5PSIwIiB3aWR0aD0iMiIgaGVpZ2h0PSIxNSI+PC9yZWN0PgogICAgICAgIDwvZz4KICAgIDwvZz4KPC9zdmc+', // purple
                '662095l': 'PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0iVVRGLTgiPz4KPHN2ZyB3aWR0aD0iMTVweCIgaGVpZ2h0PSIxNXB4IiB2aWV3Qm94PSIwIDAgMTUgMTUiIHZlcnNpb249IjEuMSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIiB4bWxuczp4bGluaz0iaHR0cDovL3d3dy53My5vcmcvMTk5OS94bGluayI+CiAgICA8dGl0bGU+R3JvdXA8L3RpdGxlPgogICAgPGcgaWQ9IlBhZ2UtMSIgc3Ryb2tlPSJub25lIiBzdHJva2Utd2lkdGg9IjEiIGZpbGw9Im5vbmUiIGZpbGwtcnVsZT0iZXZlbm9kZCI+CiAgICAgICAgPGcgaWQ9Ikdyb3VwIj4KICAgICAgICAgICAgPHJlY3QgaWQ9IlJlY3RhbmdsZSIgZmlsbD0iI0M2QjhDRiIgeD0iMCIgeT0iMCIgd2lkdGg9IjE1IiBoZWlnaHQ9IjE1Ij48L3JlY3Q+CiAgICAgICAgICAgIDxyZWN0IGlkPSJSZWN0YW5nbGUiIGZpbGwtb3BhY2l0eT0iMC4xIiBmaWxsPSIjMDAwMDAwIiB4PSIyIiB5PSIwIiB3aWR0aD0iMiIgaGVpZ2h0PSIxNSI+PC9yZWN0PgogICAgICAgICAgICA8cmVjdCBpZD0iUmVjdGFuZ2xlIiBmaWxsLW9wYWNpdHk9IjAuMSIgZmlsbD0iIzAwMDAwMCIgeD0iNyIgeT0iMCIgd2lkdGg9IjIiIGhlaWdodD0iMTUiPjwvcmVjdD4KICAgICAgICAgICAgPHJlY3QgaWQ9IlJlY3RhbmdsZSIgZmlsbC1vcGFjaXR5PSIwLjEiIGZpbGw9IiMwMDAwMDAiIHg9IjEyIiB5PSIwIiB3aWR0aD0iMiIgaGVpZ2h0PSIxNSI+PC9yZWN0PgogICAgICAgIDwvZz4KICAgIDwvZz4KPC9zdmc+',
                '169097': 'PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0iVVRGLTgiPz4KPHN2ZyB3aWR0aD0iMTVweCIgaGVpZ2h0PSIxNXB4IiB2aWV3Qm94PSIwIDAgMTUgMTUiIHZlcnNpb249IjEuMSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIiB4bWxuczp4bGluaz0iaHR0cDovL3d3dy53My5vcmcvMTk5OS94bGluayI+CiAgICA8dGl0bGU+R3JvdXA8L3RpdGxlPgogICAgPGcgaWQ9IlBhZ2UtMSIgc3Ryb2tlPSJub25lIiBzdHJva2Utd2lkdGg9IjEiIGZpbGw9Im5vbmUiIGZpbGwtcnVsZT0iZXZlbm9kZCI+CiAgICAgICAgPGcgaWQ9Ikdyb3VwIj4KICAgICAgICAgICAgPHJlY3QgaWQ9IlJlY3RhbmdsZSIgZmlsbD0iI0E5MDA2MSIgeD0iMCIgeT0iMCIgd2lkdGg9IjE1IiBoZWlnaHQ9IjE1Ij48L3JlY3Q+CiAgICAgICAgICAgIDxyZWN0IGlkPSJSZWN0YW5nbGUiIGZpbGwtb3BhY2l0eT0iMC4zIiBmaWxsPSIjRkZGRkZGIiB4PSIyIiB5PSIwIiB3aWR0aD0iMiIgaGVpZ2h0PSIxNSI+PC9yZWN0PgogICAgICAgICAgICA8cmVjdCBpZD0iUmVjdGFuZ2xlIiBmaWxsLW9wYWNpdHk9IjAuMyIgZmlsbD0iI0ZGRkZGRiIgeD0iNyIgeT0iMCIgd2lkdGg9IjIiIGhlaWdodD0iMTUiPjwvcmVjdD4KICAgICAgICAgICAgPHJlY3QgaWQ9IlJlY3RhbmdsZSIgZmlsbC1vcGFjaXR5PSIwLjMiIGZpbGw9IiNGRkZGRkYiIHg9IjEyIiB5PSIwIiB3aWR0aD0iMiIgaGVpZ2h0PSIxNSI+PC9yZWN0PgogICAgICAgIDwvZz4KICAgIDwvZz4KPC9zdmc+', // paars
                '169097l': 'PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0iVVRGLTgiPz4KPHN2ZyB3aWR0aD0iMTVweCIgaGVpZ2h0PSIxNXB4IiB2aWV3Qm94PSIwIDAgMTUgMTUiIHZlcnNpb249IjEuMSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIiB4bWxuczp4bGluaz0iaHR0cDovL3d3dy53My5vcmcvMTk5OS94bGluayI+CiAgICA8dGl0bGU+R3JvdXA8L3RpdGxlPgogICAgPGcgaWQ9IlBhZ2UtMSIgc3Ryb2tlPSJub25lIiBzdHJva2Utd2lkdGg9IjEiIGZpbGw9Im5vbmUiIGZpbGwtcnVsZT0iZXZlbm9kZCI+CiAgICAgICAgPGcgaWQ9Ikdyb3VwIj4KICAgICAgICAgICAgPHJlY3QgaWQ9IlJlY3RhbmdsZSIgZmlsbD0iI0U1QjJDRiIgeD0iMCIgeT0iMCIgd2lkdGg9IjE1IiBoZWlnaHQ9IjE1Ij48L3JlY3Q+CiAgICAgICAgICAgIDxyZWN0IGlkPSJSZWN0YW5nbGUiIGZpbGwtb3BhY2l0eT0iMC4yIiBmaWxsPSIjRkZGRkZGIiB4PSIyIiB5PSIwIiB3aWR0aD0iMiIgaGVpZ2h0PSIxNSI+PC9yZWN0PgogICAgICAgICAgICA8cmVjdCBpZD0iUmVjdGFuZ2xlIiBmaWxsLW9wYWNpdHk9IjAuMiIgZmlsbD0iI0ZGRkZGRiIgeD0iNyIgeT0iMCIgd2lkdGg9IjIiIGhlaWdodD0iMTUiPjwvcmVjdD4KICAgICAgICAgICAgPHJlY3QgaWQ9IlJlY3RhbmdsZSIgZmlsbC1vcGFjaXR5PSIwLjIiIGZpbGw9IiNGRkZGRkYiIHg9IjEyIiB5PSIwIiB3aWR0aD0iMiIgaGVpZ2h0PSIxNSI+PC9yZWN0PgogICAgICAgIDwvZz4KICAgIDwvZz4KPC9zdmc+',
                '202093': 'PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0iVVRGLTgiPz4KPHN2ZyB3aWR0aD0iMTVweCIgaGVpZ2h0PSIxNXB4IiB2aWV3Qm94PSIwIDAgMTUgMTUiIHZlcnNpb249IjEuMSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIiB4bWxuczp4bGluaz0iaHR0cDovL3d3dy53My5vcmcvMTk5OS94bGluayI+CiAgICA8dGl0bGU+R3JvdXA8L3RpdGxlPgogICAgPGcgaWQ9IlBhZ2UtMSIgc3Ryb2tlPSJub25lIiBzdHJva2Utd2lkdGg9IjEiIGZpbGw9Im5vbmUiIGZpbGwtcnVsZT0iZXZlbm9kZCI+CiAgICAgICAgPGcgaWQ9Ikdyb3VwIj4KICAgICAgICAgICAgPHJlY3QgaWQ9IlJlY3RhbmdsZSIgZmlsbD0iI0NBMDA1RCIgeD0iMCIgeT0iMCIgd2lkdGg9IjE1IiBoZWlnaHQ9IjE1Ij48L3JlY3Q+CiAgICAgICAgICAgIDxyZWN0IGlkPSJSZWN0YW5nbGUiIGZpbGwtb3BhY2l0eT0iMC4yIiBmaWxsPSIjMDAwMDAwIiB4PSIyIiB5PSIwIiB3aWR0aD0iMiIgaGVpZ2h0PSIxNSI+PC9yZWN0PgogICAgICAgICAgICA8cmVjdCBpZD0iUmVjdGFuZ2xlIiBmaWxsLW9wYWNpdHk9IjAuMiIgZmlsbD0iIzAwMDAwMCIgeD0iNyIgeT0iMCIgd2lkdGg9IjIiIGhlaWdodD0iMTUiPjwvcmVjdD4KICAgICAgICAgICAgPHJlY3QgaWQ9IlJlY3RhbmdsZSIgZmlsbC1vcGFjaXR5PSIwLjIiIGZpbGw9IiMwMDAwMDAiIHg9IjEyIiB5PSIwIiB3aWR0aD0iMiIgaGVpZ2h0PSIxNSI+PC9yZWN0PgogICAgICAgIDwvZz4KICAgIDwvZz4KPC9zdmc+', // pink
                '202093l': 'PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0iVVRGLTgiPz4KPHN2ZyB3aWR0aD0iMTVweCIgaGVpZ2h0PSIxNXB4IiB2aWV3Qm94PSIwIDAgMTUgMTUiIHZlcnNpb249IjEuMSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIiB4bWxuczp4bGluaz0iaHR0cDovL3d3dy53My5vcmcvMTk5OS94bGluayI+CiAgICA8dGl0bGU+R3JvdXA8L3RpdGxlPgogICAgPGcgaWQ9IlBhZ2UtMSIgc3Ryb2tlPSJub25lIiBzdHJva2Utd2lkdGg9IjEiIGZpbGw9Im5vbmUiIGZpbGwtcnVsZT0iZXZlbm9kZCI+CiAgICAgICAgPGcgaWQ9Ikdyb3VwIj4KICAgICAgICAgICAgPHJlY3QgaWQ9IlJlY3RhbmdsZSIgZmlsbD0iI0VGQjJDRSIgeD0iMCIgeT0iMCIgd2lkdGg9IjE1IiBoZWlnaHQ9IjE1Ij48L3JlY3Q+CiAgICAgICAgICAgIDxyZWN0IGlkPSJSZWN0YW5nbGUiIGZpbGwtb3BhY2l0eT0iMC4xIiBmaWxsPSIjMDAwMDAwIiB4PSIyIiB5PSIwIiB3aWR0aD0iMiIgaGVpZ2h0PSIxNSI+PC9yZWN0PgogICAgICAgICAgICA8cmVjdCBpZD0iUmVjdGFuZ2xlIiBmaWxsLW9wYWNpdHk9IjAuMSIgZmlsbD0iIzAwMDAwMCIgeD0iNyIgeT0iMCIgd2lkdGg9IjIiIGhlaWdodD0iMTUiPjwvcmVjdD4KICAgICAgICAgICAgPHJlY3QgaWQ9IlJlY3RhbmdsZSIgZmlsbC1vcGFjaXR5PSIwLjEiIGZpbGw9IiMwMDAwMDAiIHg9IjEyIiB5PSIwIiB3aWR0aD0iMiIgaGVpZ2h0PSIxNSI+PC9yZWN0PgogICAgICAgIDwvZz4KICAgIDwvZz4KPC9zdmc+'
            };
            var ministerie_identifiers = {
                '1': '#999999',
                '2A': '#f092cd',
                '2B': '#b4b4b4',
                '3': '#a90061',
                '4': '#d52b1e',
                '5': '#39870c',
                '6': '#8fcae7',
                '8': '#777c00',
                '9': '#007bc7',
                '9A': '#cccccc',
                '9B': '#007bc7',
                '10': '#42145f',
                '12': '#f9e11e',
                '13': '#275937',
                '14': '#72be19',
                '15': '#ca005d',
                '16': '#e17000',
                '17': '#76d2b6',
                '18': '#D47735',
                '50': '#01689b',
                'I': '#999999',
                'IIA': '#f092cd',
                'IIB': '#b4b4b4',
                'III': '#a90061',
                'IV': '#d52b1e',
                'V': '#39870c',
                'VI': '#8fcae7',
                'VII': '#d52b1e',
                'VIII': '#777c00',
                'IX': '#007bc7',
                'IXA': '#cccccc',
                'IXB': '#007bc7',
                'X': '#42145f',
                'XII': '#f9e11e',
                'XIII': '#275937',
                'XIV': '#72be19',
                'XV': '#ca005d',
                'XVI': '#e17000',
                'XVII': '#76d2b6',
                'XVIII': '#D47735',
                'A': '#ffb612',
                'B': '#01689b',
                'C': '#01689b',
                'F': '#535353',
                'H': '#696969',
                'J': '#94710a'
            };
            var ministerie_colors = {
                'Volksgezondheid, Welzijn en Sport': '#e17000',
                'Sociale Zaken en Werkgelegenheid': '#ca005d',
                'Nationale Schuld': '#cccccc',
                'Onderwijs, Cultuur en Wetenschap': '#777c00',
                'Gemeentefonds': '#01689b',
                'Veiligheid en Justitie': '#8fcae7',
                'Justitie en Veiligheid': '#8fcae7',
                'Defensie': '#42145f',
                'Buitenlandse Zaken': '#39870c',
                'Infrastructuur en Milieu': '#f9e11e',
                'Infrastructuur en Waterstaat': '#f9e11e',
                'Financiën': '#007bc7',
                'Binnenlandse Zaken en Koninkrijksrelaties': '#d52b1e',
                'Infrastructuurfonds': '#ffb612',
                'Economische Zaken': '#275937',
                'Economische Zaken en Klimaat': '#275937',
                'Buitenlandse Handel & Ontwikkelingssamenwerking': '#76d2b6',
                'Provinciefonds': '#01689b',
                'Landbouw, Natuur en Voedselkwaliteit': '#72be19',
                'Deltafonds': '#94710a',
                'Koninkrijksrelaties': '#d52b1e',
                'Staten Generaal': '#f092cd',
                'Overige Hoge Colleges van Staat, Kabinetten van de Gouverneurs en de Kiesraad': '#b4b4b4',
                'Algemene Zaken': '#a90061',
                'Diergezondheidsfonds': '#535353',
                'De Koning': '#999999',
                'BES-fonds': '#696969',
                'Kabinet van de Koning': '#d52b1e',
                'Commissie van Toezicht betreffende de Inlichtingen- en Veiligheidsdiensten': '#e17000',
                'Wonen en Rijksdienst': '#94710a'
            };
            var ministerie_abbreviation = {
                'VWS': 'Volksgezondheid, Welzijn en Sport',
                'SZW': 'Sociale Zaken en Werkgelegenheid',
                'NS': 'Nationale Schuld',
                'OCW': 'Onderwijs, Cultuur en Wetenschap',
                'VENJ': 'Veiligheid en Justitie',
                'JENV': 'Justitie en Veiligheid',
                'DEF': 'Defensie',
                'BZ': 'Buitenlandse Zaken',
                'IENM': 'Infrastructuur en Milieu',
                'IENW': 'Infrastructuur en Waterstaat',
                'FIN': 'Financiën',
                'BZK': 'Binnenlandse Zaken en Koninkrijksrelaties',
                'EZ': 'Economische Zaken',
                'EZK': 'Economische Zaken en Klimaat',
                'LNV': 'Landbouw, Natuur en Voedselkwaliteit',
                'AZ': 'Algemene Zaken'
            };
            var curKeyDown = false;

            /*
            // set theme
            */
            var chart_colors = [];
            for (let c in minfin_colors) {
                chart_colors.push(minfin_colors[c]);
            }
            function am4themes_minfin(target) {
                if (target._className == 'SpriteState' && target.name == 'inactive') {
                }
                else if (target instanceof am4core.ColorSet) {
                    target.list = chart_colors;
                    target.startIndex = 0;
                }
                if (target instanceof am4core.InterfaceColorSet) {
                    target.setFor("text", am4core.color("#000000"));
                }
            }
            /*
            // assign color set
            */
            am4core.useTheme(am4themes_minfin);




            /*
            // prepare global vars
            */
            var minfin_data = [];
            var containers = [];
            var charts = [];
            var series = [];
            var discs = [];
            var labels = [];
            var mousePos = {
                x: 0,
                y: 0,
                item: false,
                px: false,
                py: false
            };
            // Popup delay timers
            var openTimer = false;
            var closeTimer = false;
            // sorter
            var sortVal = ['value', 'desc', false];

            /*
            // create chart
            */
            function _minfin_chart(id, type, data, options, meta) {
                //console.log('data', data)

                // shuffle series data for clustered columns
                if (options.series) {
                    var data_ = [];
                    for (var s in options.series) {
                        for (var d in data) {
                            if (data[d]['identifier'] == options.series[s]) {
                                data_.push(data[d]);
                            }
                        }
                    }
                    for (var d in data) {
                        if (options.series.indexOf(data[d]['identifier']) == -1) {
                            data_.push(data[d]);
                        }
                    }
                    var data = [];
                    for (var d in data_) {
                        data.push(data_[d]);
                    }
                }

                // prepare options
                if (typeof (options) == 'undefined') {
                    options = {};
                }

                // sort data
                var val = typeof (options.value) != 'undefined' ? options.value : "value";
                if (options.sort) {
                sortVal[2] = options.collect && options.collect['title'] ? options.collect['title'] : false;
                    sortVal[1] = options.sort;
                    sortVal[0] = val;
                    data.sort(sortArray);
                }
                // remove null values
                for (let d in data) {
                    if (!data[d][val]) {
                        data[d][val] = 0;
                    }
                }

                // add classnames
                if (document.getElementById(id)) {
                    document.getElementById(id).classList.add('chart_canvas');
                }
                if (document.getElementById(id + '_legend')) {
                    document.getElementById(id + '_legend').classList.add('chart_legend');
                }

                // increase chartId
                var chartId = charts.length;
                document.getElementById(id).setAttribute('chartId', chartId);

                // set id to navigation
                var nav = document.getElementById(id + '_navigation');
                if (nav) {
                    nav.setAttribute('chartId', chartId);
                }
                nav = document.getElementById(id + '_navigation_top');
                if (nav) {
                    nav.setAttribute('chartId', chartId);
                }
                // create canvas
                if (containers[chartId]) {
                    containers[chartId].dispose();
                }
                containers[chartId] = am4core.create(id, am4core.Container);
                var container = containers[chartId];
                container.width = am4core.percent(100);
                container.height = am4core.percent(100);

                // fill data object
                minfin_data[chartId] = {
                    data: data,
                    options: options
                }

                // get some meta data from options
                if (!meta) meta = {}
                if (!meta.title && options.title) meta.title = options.title;
                if (!meta.previous && options.previous) meta.previous = options.previous;
                if (!meta.percentage && options.percentage) meta.percentage = options.percentage;
                if (!meta.legend) meta.legend = options.legend ? options.legend : false;

                // split long previous title
                if (meta.previous) {
                    if (minfin_api.path && minfin_api.path['corona'] && minfin_api.path['identifier'] && minfin_api.path['identifier'] != '0') {
                        meta.previous = options.back_title;
                    }
                    var lines = [meta.previous];
                    if (lines[0].length > 20) {
                        var line = lines[0].split(' ');
                        lines[0] = '';
                        var ln = 0;
                        for (let l in line) {
                            if (lines[ln].length + line[l].length > 20) {
                                ln++;
                                lines[ln] = '';
                            }
                            if (lines[ln].length) lines[ln] += ' ';
                            lines[ln] += line[l];
                        }
                    }
                    if (lines[2] && lines[2].length) lines[1] += '...';
                    meta.previous = lines;
                } else {
                    meta.previous = '';
                }

                // save some options in meta
                if (options.currency) meta['currency'] = options.currency;
                if (options.divider) meta['divider'] = options.divider;
                if (options.multiplier) meta['multiplier'] = options.multiplier;
                if (options.round) meta['round'] = options.round;
                if (options.decimals) meta['decimals'] = options.decimals;

                // add metadata to data object
                minfin_data[chartId]['meta'] = {};
                for (let m in meta) {
                    minfin_data[chartId]['meta'][m] = meta[m];
                }

                // create am4charts by type
                switch (type) {
                    case 'sankey':
                        // create sankey diagram
                        charts[chartId] = container.createChild(am4charts.SankeyDiagram);
                        container.width = am4core.percent(100);
                        container.height = am4core.percent(100);
                        chart = charts[chartId];
                        chart.domId = id;
                        chart.local = typeof (options.local) != 'undefined' ? options.local : false;
                        chart.datatype = typeof (options.datatype) != 'undefined' ? options.datatype : false;
                        // set language
                        chart.language.locale = am4lang_nl_NL;
                        // set data
                        chart.data = data;
                        // color steps
                        chart.colors.step = 1;
                        // no animation
                        chart.interpolationDuration = 0;

                        // highlight if chart is ready
                        chart.events.on("ready", function (ev) {
                            // move page to anchorlink
                            if (minfin_api.anchor != '' && document.querySelector('.' + minfin_api.anchor)) {
                                var anchor = document.querySelector('.' + minfin_api.anchor).offsetTop;
                                window.scrollTo(0, anchor);
                            }
                            // remove spinner
                            document.querySelector('#' + charts[chartId].domId).classList.remove('spin');
                            // prepare sliderbox
                            if (minfin_data[chartId].options['slider']) {

                                // set chartId in corresponding slider
                                minfin_data[chartId].options['slider'].obj.set({
                                    chartId: chartId
                                });
                            }
                            // remove tabindex
                            removeTabIndex();
                            resize_graph(200);
                        });

                        // prepare data
                        chart.dataFields.fromName = "from";
                        chart.dataFields.toName = "to";
                        chart.dataFields.value = "value";

                        // fill
                        chart.properties.fillOpacity = 1;
                        // hover state
                        var hoverState = chart.links.template.states.create("hover");
                        hoverState.properties.fillOpacity = 0.5;

                        // for right-most label to fit
                        chart.paddingRight = 80;

                        // prepare data
                        series[series.length] = chart;
                        var serie = series[series.length - 1];

                        // set some option vars
                        serie.chartId = chartId;

                        // Configure nodes
                        var nodeTemplate = serie.nodes.template;
                        nodeTemplate.width = 16;
                        nodeTemplate.events.off("hit"); // no toggle
                        nodeTemplate.draggable = false; // not dragable
                        // labels
                        nodeTemplate.nameLabel.height = undefined;
                        nodeTemplate.nameLabel.label.hideOversized = true;
                        nodeTemplate.nameLabel.label.width = 300;
                        nodeTemplate.nameLabel.label.truncate = false;
                        nodeTemplate.nameLabel.label.wrap = true;
                        nodeTemplate.nameLabel.locationX = 1;
                        nodeTemplate.nameLabel.label.fill = am4core.color("#000");
                        nodeTemplate.nameLabel.label.fontWeight = "regular";

                        //nodeTemplate.nameLabel.label.align = 'right';

                        //nodeTemplate.nameLabel.horizontalCenter = "right";
                        //nodeTemplate.nameLabel.label.horizontalCenter = "right";
                        nodeTemplate.nameLabel.label.textAlign = "end";
                        nodeTemplate.nameLabel.width = undefined;


                        nodeTemplate.nameLabel.label.properties.align = 'right';

                        // set label left or right according to level
                        nodeTemplate.nameLabel.adapter.add("locationX", function (location, target) {
                            if (typeof (minfin_data[chartId].options.targetfrom) == 'undefined') {
                                switch (target.parent.level) {
                                    case 0:
                                        return minfin_data[chartId].options ? 1 : parseInt(290 / -15);
                                        break;
                                    default:
                                        return minfin_data[chartId].options ? parseInt(290 / -15) : 1;
                                }
                            } else {
                                return minfin_data[chartId].options.targetfrom ? 1 : parseInt(290 / -15);
                            }
                        });
                        nodeTemplate.nameLabel.label.adapter.add("textAlign", function (location, target) {
                            if (typeof (minfin_data[chartId].options.targetfrom) == 'undefined') {
                                switch (target.parent._parent.properties.level) {
                                    case 0:
                                        return minfin_data[chartId].options ? "start" : "end";
                                        break;
                                    default:
                                        return minfin_data[chartId].options ? "end" : "start";
                                }
                            } else {
                                return minfin_data[chartId].options.targetfrom ? "start" : "end";
                            }
                        });

                        // paddings
                        serie.paddingRight = 0;
                        serie.paddingTop = 40;
                        serie.paddingBottom = 40;

                        // links (flow from left to right)
                        var linkTemplate = serie.links.template;
                        linkTemplate.tooltipText = ""; // disable default tooltip
                        linkTemplate.colorMode = "gradient";
                        linkTemplate.fillOpacity = 0.2;
                        linkTemplate.tension = 0.75;
                        linkTemplate.controlPointDistance = 0.2;
                        linkTemplate.strokeWidth = 0.8;
                        linkTemplate.size = 0.8;
                        // middle stroke, probably needs setting
                        linkTemplate.middleLine.strokeOpacity = 0;
                        linkTemplate.middleLine.stroke = am4core.color("#555");
                        linkTemplate.middleLine.strokeWidth = 1;

                        // map data
                        serie.dataFields.fromName = typeof (options.from) != 'undefined' ? options.from : "from";
                        serie.dataFields.toName = typeof (options.to) != 'undefined' ? options.to : "to";
                        serie.dataFields.value = typeof (options.value) != 'undefined' ? options.value : "value"; chart.dataFields.color = "nodeColor";

                        for (let cd in serie.data) {
                            if (options.divider) {
                                serie.data[cd][serie.dataFields.value] = divide_number(serie.data[cd][serie.dataFields.value], options);
                                // and previous year
                                if (serie.data[cd][serie.dataFields.value - 1]) {
                                    serie.data[cd][serie.dataFields.value - 1] = divide_number(serie.data[cd][serie.dataFields.value - 1], options);
                                }
                            }
                            serie.data[cd].value = serie.data[cd][serie.dataFields.value];
                            serie.data[cd].data_year = serie.dataFields.value; //'YO'
                        }

                        // set default value
                        for (let cd in serie.data) {
                            // prevent null values
                            if (!serie.data[cd].value) {
                                serie.data[cd].value = 0;
                            }
                            serie.data[cd].defaultValue = serie.data[cd].value;
                        }

                        // Create default active state
                        var defaultLinkState = linkTemplate.middleLine.states.create("default");
                        defaultLinkState.properties.strokeOpacity = 0;
                        defaultLinkState.transitionDuration = 250;
                        var defaultState = linkTemplate.states.create("default");
                        defaultState.properties.shiftRadius = 0;
                        defaultState.properties.scale = 1;
                        defaultState.properties.fillOpacity = 0.2;
                        defaultState.transitionDuration = 250;

                        // Create active active state
                        var activeState = linkTemplate.states.create("active");
                        activeState.properties.shiftRadius = 0;
                        activeState.properties.scale = 1;
                        activeState.properties.fillOpacity = 0.7;
                        activeState.transitionDuration = 250;

                        // Create inactive state
                        var inactiveLinkState = linkTemplate.middleLine.states.create("inactive");
                        inactiveLinkState.properties.strokeOpacity = 0.2;
                        inactiveLinkState.transitionDuration = 250;
                        var inactiveState = linkTemplate.states.create("inactive");
                        inactiveState.properties.fillOpacity = 0;
                        inactiveState.properties.shiftRadius = 0;
                        inactiveState.properties.scale = 1;
                        inactiveState.properties.hoverable = false;
                        inactiveState.properties.clickable = true;
                        inactiveState.properties.cursorOverStyle = am4core.MouseCursorStyle.default;
                        inactiveState.transitionDuration = 250;

                        // Create hover state
                        var hoverState = linkTemplate.states.create("hover");
                        hoverState.properties.shiftRadius = 0;
                        hoverState.properties.scale = 1;
                        hoverState.properties.fillOpacity = 0.5
                        hoverState.transitionDuration = 250;

                        // container for overlay info
                        var topContainer = chart.chartContainer.createChild(am4core.Container);
                        topContainer.layout = "absolute";
                        topContainer.toFront();
                        topContainer.paddingBottom = 15;
                        topContainer.width = am4core.percent(100);
                        topContainer.backgroundColor = am4core.color("black");

                        // add divider info
                        if (options.divider || options.multiplier) {
                            labels[chartId] = {
                                divider: topContainer.createChild(am4core.Label)
                            }
                            var label = labels[chartId]['divider'];
                            var multiplier_text = parseInt(options.multiplier) * parseInt(options.divider);
                            label.text = multiplier_text == 1000000000 ? "Alle bedragen in miljarden" : multiplier_text == 1000000 ? "Alle bedragen in miljoenen" : multiplier_text > 1 ? "Alle bedragen x" + multiplier_text : '';
                            if (csv_date && csv_date != '') {
                                label.text += String.fromCharCode(10) + String.fromCharCode(13) + 'Laatste update ' + csv_date;
                            }
                            label.fontSize = '0';
                            label.fontFamily = _minfin_charts_options.font;
                            label.align = "left";
                            label.verticalCenter = false;
                            label.x = am4core.percent(50);
                            label.y = -35;
                            label.id = "sankey_label_divider";
                        }

                        // qwerty
                        // EVENTS
                        linkTemplate.events.on("over", function (event) {
                            event.target.setState(event.target.state);
                            show_popup(serie.chartId, event.target._dataItem._index, false, event.target);
                            // cursor
                            serie.links.each(function (item) {
                                if (item == event.target) {
                                    if (minfin_data[chartId].meta.quicklink && !isTouchDevice()) {
                                        if (item.state != 'inactive' && item._dataItem._dataContext.link) {
                                            if (item._dataItem._dataContext.link.indexOf('ext:') == 0) {
                                                item.cursorOverStyle = am4core.MouseCursorStyle.pointer;
                                            } else if (item._dataItem._dataContext.link.indexOf('internal:') == 0) {
                                                if ((typeof (item._dataItem._dataContext.self_info) != 'undefined' && item._dataItem._dataContext.self_info) || (typeof (item._dataItem._dataContext.child_info) != 'undefined' && item._dataItem._dataContext.child_info)) {
                                                    item.cursorOverStyle = am4core.MouseCursorStyle.pointer;
                                                }
                                            } else {
                                                item.cursorOverStyle = am4core.MouseCursorStyle.pointer;
                                            }
                                        }
                                    }
                                }
                            });
                            return true;
                        });
                        linkTemplate.events.on("out", function (event) {
                            sluit_popup();
                            setTimeout(function (e) {
                                if (event.target.state != 'undefined') {
                                    e.target.setState(event.target.state);
                                } else {
                                    e.target.setState('default');
                                }
                            }, 20, event);
                        });
                        linkTemplate.events.on("hit", function (event) {
                            serie.links.each(function (item) {
                                if (item == event.target) {
                                    if (minfin_data[chartId].meta.quicklink && !isTouchDevice()) {
                                        if (item.state != 'inactive' && item._dataItem._dataContext.link) {
                                            if (item._dataItem._dataContext.link.indexOf('ext:') == 0) {
                                                var link = item._dataItem._dataContext.link;
                                                location.href = link.replace(/ext:/, '');
                                            } else if (item._dataItem._dataContext.link.indexOf('json:') == 0 || item._dataItem._dataContext.link.indexOf('csv:') == 0) {
                                                if ((typeof (item._dataItem._dataContext.self_info) != 'undefined' && item._dataItem._dataContext.self_info) || (typeof (item._dataItem._dataContext.child_info) != 'undefined' && item._dataItem._dataContext.child_info)) {
                                                    var link = item._dataItem._dataContext.link;
                                                    var parts = link.split(':');
                                                    for (var ds in minfin_api['drupal_structure']) {
                                                        if (!minfin_api['path'][minfin_api['drupal_structure'][ds]]) {
                                                            minfin_api['path'][minfin_api['drupal_structure'][ds]] = parts[parts.length - 1];
                                                            reloadnow(serie.chartId);
                                                            break;
                                                        }
                                                    }
                                                }
                                            } else if (item._dataItem._dataContext.link.indexOf('internal:') == 0) {
                                                if ((typeof (item._dataItem._dataContext.self_info) != 'undefined' && item._dataItem._dataContext.self_info) || (typeof (item._dataItem._dataContext.child_info) != 'undefined' && item._dataItem._dataContext.child_info)) {
                                                    var link = item._dataItem._dataContext.link;
                                                    var parts = link.split('/');
                                                    setDrupalPath('identifier', parts[parts.length - 1]);
                                                    reloadnow(serie.chartId);
                                                }
                                            } else {
                                                var url = item._dataItem._dataContext.link.replace(/\/\[([0-9]{4})\]\//, '/' + item._dataItem._dataContext.data_year + '/');
                                                location.href = url + addQuery('graph', '#');
                                            }
                                        }
                                    } else {
                                        mousePos.item = item;
                                        var reset = setSlice(chartId, item._dataItem._index);
                                        if (reset) {
                                            sluit_popup(true, false);
                                        }
                                        else {
                                            open_popup(chartId, item._dataItem._index, item);
                                        }
                                    }
                                }
                            });
                        });

                        break;
                    case 'pie':
                        // create pie
                        charts[chartId] = container.createChild(am4charts.PieChart);
                        chart = charts[chartId];
                        chart.domId = id;
                        chart.local = typeof (options.local) != 'undefined' ? options.local : false;
                        chart.datatype = typeof (options.datatype) != 'undefined' ? options.datatype : false;
                        // set pie size
                        chart.radius = am4core.percent(86);
                        chart.innerRadius = am4core.percent(typeof (options.size) != 'undefined' ? Math.round(options.size * 0.84) : 54);
                        // set language
                        chart.language.locale = am4lang_nl_NL;
                        // set data
                        chart.data = data;
                        // color steps
                        chart.colors.step = 1;

                        // highlight if chart is ready
                        chart.events.on("ready", function (ev) {
                            if (minfin_api.data.category) preSelect(chartId);
                            // move page to anchorlink
                            if (minfin_api.anchor != '' && document.querySelector('.' + minfin_api.anchor)) {
                                var anchor = document.querySelector('.' + minfin_api.anchor).offsetTop;
                                window.scrollTo(0, anchor);
                            }
                            // remove spinner
                            document.querySelector('#' + charts[chartId].domId).classList.remove('spin')
                            // show bar/donut switch
                            var carousel = document.querySelector('#' + charts[chartId].domId).parentElement.parentElement;
                            carousel = carousel.querySelector('#chart_nav_carousel');
                            if (carousel && (carousel.classList.contains('bar') || carousel.classList.contains('donut')) && !carousel.classList.contains('noswitch')) {
                                carousel.style.display = 'block';
                                carousel.setAttribute('tabindex', 6);
                                if (carousel.classList.contains('bar')) {
                                    carousel.setAttribute('title', 'Toon staafdiagram');
                                }
                                if (carousel.classList.contains('donut')) {
                                    carousel.setAttribute('title', 'Toon donut');
                                }
                            }
                            // prepare sliderbox
                            if (minfin_data[chartId].options['slider']) {

                                // set chartId in corresponding slider
                                minfin_data[chartId].options['slider'].obj.set({
                                    chartId: chartId
                                });

                                var sliderbox = minfin_data[chartId].options['slider']['id'] ? '#' + minfin_data[chartId].options['slider']['id'] : '.manual_selector';
                                $('#' + minfin_data[chartId].options['target'] + '_navigation ' + sliderbox).parent().parent().css('display', 'block');
                                $('#' + minfin_data[chartId].options['target'] + '_navigation ' + sliderbox).parent().css('display', 'block');
                                $('#' + minfin_data[chartId].options['target'] + '_navigation_top ' + sliderbox).parent().parent().css('display', 'block');
                                $('#' + minfin_data[chartId].options['target'] + '_navigation_top ' + sliderbox).parent().css('display', 'block');
                            }
                            // remove tabindex
                            removeTabIndex();
                            resize_graph(200);
                        });

                        // prepare series
                        series[series.length] = chart.series.push(new am4charts.PieSeries());
                        var serie = series[series.length - 1];

                        // set some option vars
                        serie.chartId = chartId;

                        // map data to series
                        var smooth = true; // patterns cannot animate smooth
                        serie.dataFields.category = typeof (options.category) != 'undefined' ? options.category : "category";
                        serie.category = serie.dataFields.category;
                        serie.dataFields.value = typeof (options.value) != 'undefined' ? options.value : "value";
                        //if (serie.dataFields.value != 'value') {
                            for (let cd in chart.data) {
                                if (chart.data[cd].extra && chart.data[cd].extra.indexOf('pattern') > -1) smooth = false; // there is a pattern in the current dataset
                                if (options.divider) {
                                    chart.data[cd][serie.dataFields.value] = divide_number(chart.data[cd][serie.dataFields.value], options);
                                    // and previous year
                                    if (chart.data[cd][serie.dataFields.value - 1]) {
                                        chart.data[cd][serie.dataFields.value - 1] = divide_number(chart.data[cd][serie.dataFields.value - 1], options);
                                    }
                                }
                                chart.data[cd].value = chart.data[cd][serie.dataFields.value];
                                chart.data[cd].data_year = serie.dataFields.value; //'YO'
                            }
                        //}
                        // set default value
                        for (let cd in chart.data) {
                            // prevent null values
                            if (!chart.data[cd].value) {
                                chart.data[cd].value = 0;
                            }
                            chart.data[cd].defaultValue = chart.data[cd].value;
                        }

                        // disable labels, ticks and tooltip
                        serie.labels.template.disabled = true;
                        serie.ticks.template.disabled = true;
                        serie.slices.template.tooltipText = false;

                        // set stroke
                        serie.slices.template.stroke = _minfin_charts_options.stroke_color;
                        serie.slices.template.strokeWidth = _minfin_charts_options.stroke_width;
                        serie.slices.template.strokeOpacity = 1;
                        serie.slices.template.fillOpacity = 1;

                        serie.slices.template.adapter.add("fill", function (fill, target) {
                            if (fill && target && target._dataItem && target._dataItem._dataContext && target._dataItem._dataContext.extra) {
                                if (target._dataItem._dataContext.extra.indexOf('pattern') > -1) { // pattern
                                    var col = fill._className == 'Pattern' ? fill.properties.fill : fill;
                                    col = col._value.r + '' + col._value.g + '' + col._value.b;
                                    if (col != '000' && col != '230230230') {
                                        var pattern = new am4core.RectPattern();
                                        pattern.width = 15
                                        pattern.height = 15;
                                        pattern.rectWidth = 0;
                                        pattern.rectHeight = 0;

                                        var image = new am4core.Image();
                                        image.href = "data:image/svg+xml;charset=utf-8;base64," + minfin_patterns[col];
                                        image.width = 15;
                                        image.height = 15;
                                        pattern.addElement(image.element);

                                        return pattern;
                                    }
                                }
                            }
                            return fill;
                        });

                        if (!smooth) {
                            // Create default state to colour the pattern
                            var defaultState = serie.slices.template.states.create("default");
                            defaultState.adapter.add("fill", function (fill, target) {
                                if (fill && target && target._dataItem && target._dataItem._dataContext && target._dataItem._dataContext.extra) {
                                    if (target._dataItem._dataContext.extra.indexOf('pattern') > -1) {
                                        var col = fill._className == 'Pattern' ? fill.properties.fill : fill;
                                        var pattern = new am4core.RectPattern();
                                        // pattern.rectWidth = 4;
                                        // pattern.rectHeight = 3;
                                        // pattern.width = 4
                                        // pattern.height = 4;
                                        // pattern.strokeWidth = 0;
                                        // pattern.fill = col;

                                        pattern.width = 15
                                        pattern.height = 20;
                                        pattern.rectWidth = 0;
                                        pattern.rectHeight = 0;
                                        var image = new am4core.Image();
                                        image.href = "data:image/svg+xml;charset=utf-8;base64," + minfin_patterns[col];
                                        image.width = 15;
                                        image.height = 20;
                                        pattern.addElement(image.element);


                                        return pattern;
                                    }
                                }
                                return fill;
                            });
                            defaultState.transitionDuration = 1;
                        }

                        // Create default active state
                        var activeState = serie.slices.template.states.create("active");
                        activeState.properties.shiftRadius = 0;
                        activeState.properties.scale = 1;
                        activeState.adapter.add("fill", function (fill, target) {
                            return fill;
                        });
                        if (!smooth) {
                            activeState.transitionDuration = 1;
                        }

                        // Create inactive state
                        var inactiveState = serie.slices.template.states.create("inactive");
                        inactiveState.properties.fill = _minfin_charts_options.inactive_color;
                        inactiveState.properties.fillOpacity = 1;
                        inactiveState.properties.shiftRadius = 0;
                        inactiveState.properties.hoverable = false;
                        inactiveState.properties.clickable = true;
                        inactiveState.properties.cursorOverStyle = am4core.MouseCursorStyle.default;
                        if (!smooth) {
                            inactiveState.transitionDuration = 1;
                        }

                        // Create hover state
                        var hoverState = serie.slices.template.states.create("hover");
                        hoverState.properties.scale = 1;
                        hoverState.properties.shiftRadius = 0;
                        hoverState.adapter.add("fill", function (fill, target) {
                            //return target.sprite.state == 'inactive' ? _minfin_charts_options.inactive_color : fill; //rpattern;
                            //return am4core.color("blue");
                        });
                        if (!smooth) {
                            hoverState.transitionDuration = 1;
                        }

                        // No ticks
                        serie.ticks.template.disabled = false;

                        // This creates initial animation
                        serie.hiddenState.properties.opacity = 0;
                        serie.hiddenState.properties.endAngle = 90;
                        serie.hiddenState.properties.startAngle = 90;

                        // container for overlay info
                        var topContainer = chart.chartContainer.createChild(am4core.Container);
                        topContainer.layout = "absolute";
                        topContainer.toBack();
                        topContainer.paddingBottom = 15;
                        topContainer.width = am4core.percent(100);

                        // Add center disc
                        if (typeof (options.disc) == 'undefined' || options.disc) {
                            discs[chartId] = serie.createChild(am4core.Circle);
                            var disc = discs[chartId];
                            disc.radius = 100;
                            disc.id = "centerdiv";
                            disc.chartId = chartId;
                            disc.strokeWidth = 0;
                            disc.fill = am4core.color("#ffffff");


                            // disc.events.on("over", function (event) {
                            //     if (openTimer) clearTimeout(openTimer);
                            //     openTimer = setTimeout(function () {
                            //         var target = document.getElementById("chart_popup");
                            //         var open = target ? parseInt(target.getAttribute('open')) : '-';
                            //         if (isNaN(open)) label.text = "reset";
                            //     }, _minfin_charts_options.delay);
                            // });
                            // disc.events.on("out", function (event) {
                            //     label.text = "€ {values.value.sum}";
                            // });
                            // disc.events.on("hit", function (event) {
                            //     //location.href = location.protocol + '//' + location.host + location.pathname + '?year=2019';
                            // });

                            disc.events.on("over", function (event) {
                                var chartId = event.target.chartId;
                                if (minfin_api.data.chapter || (minfin_api.path && minfin_api.path['corona'] && minfin_api.path['identifier'] && minfin_api.path['identifier'] != '0')) {
                                    if (openTimer) {
                                        clearTimeout(openTimer);
                                    }
                                    openTimer = setTimeout(function (chartId) {
                                        var target = document.getElementById("chart_popup");
                                        var open = target ? parseInt(target.getAttribute('open')) : '-';
                                        if (isNaN(open)) {
                                            var shift = minfin_data[chartId].meta.previous[1] && minfin_data[chartId].meta.previous[1] != '' ? 15 : 27;
                                            var chart = document.getElementById(charts[chartId].domId);
                                            var bbox = chart.getBoundingClientRect();
                                            var max = Math.min(bbox.width, bbox.height);
                                            var m = max / 460;

                                            labels[chartId].value.text = "Terug naar";
                                            labels[chartId].percentage.text = "";
                                            labels[chartId].titlerow1.text = minfin_data[chartId].meta.previous[0];
                                            labels[chartId].titlerow2.text = minfin_data[chartId].meta.previous[1];

                                            labels[chartId].value.y = (m * -(42 - shift));
                                            labels[chartId].titlerow1.y = (m * -(14 - shift));
                                            labels[chartId].titlerow2.y = (m * (15 + shift));
                                        }
                                    }, _minfin_charts_options.delay, chartId);
                                }
                            });
                            disc.events.on("out", function (event) {
                                var chartId = event.target.chartId;

                                var target = document.getElementById("chart_popup");
                                var open = target ? parseInt(target.getAttribute('open')) : '-';
                                if (isNaN(open)) {
                                    var sum = "€ {values.value.sum}";
                                    if (minfin_data[chartId].meta.range) {
                                        sum = minfin_data[chartId].meta.range;
                                    } else if (minfin_data[chartId].meta.legend_total) {
                                        sum = minfin_data[chartId].meta.legend_total;
                                    } else {
                                        sum = format_number(sum, minfin_data[chartId].meta);
                                    }
                                    labels[chartId].value.text = sum;

                                    if (minfin_data[chartId].meta.percentage) {
                                        var perc = minfin_data[chartId].meta.percentage - 100;
                                        perc = perc.toLocaleString('nl-NL', { style: 'decimal', maximumFractionDigits: 2 })
                                        labels[chartId].percentage.text = "Ten opzichte van " + (minfin_api.data.year - 1) + " " + perc + "%";
                                    }
                                    labels[chartId].titlerow1.text = minfin_data[chartId].meta.titlerow[0];
                                    labels[chartId].titlerow2.text = minfin_data[chartId].meta.titlerow[1];
                                    resize_graph();
                                }
                            });
                            disc.events.on("hit", function (event) {
                                if (minfin_api.data.chapter) {
                                    location.href = getDrupalPath(true) + addQuery(['graph', '#']);
                                } else if (minfin_api.path && minfin_api.path['corona'] && minfin_api.path['identifier'] && minfin_api.path['identifier'] != '0') {
                                    // old version
                                    setDrupalPath('identifier', options.previous);
                                    reloadnow(disc.chartId);
                                } else {
                                    // new version
                                    var index = 0;
                                    for (var ds in minfin_api['drupal_structure']) {
                                        if (minfin_api['path'][minfin_api['drupal_structure'][ds]]) {
                                            index = ds;
                                        }
                                    }
                                    var link = $('#' + charts[disc.chartId]['domId'] + '_navigation').find('#chart_nav_return').css('visibility');
                                    if (link != 'hidden') {
                                        setDrupalPath('chartId', disc.chartId);
                                    }
                                }
                            });
                            var discFilter = disc.filters.push(new am4core.DropShadowFilter());
                            discFilter.dx = 0;
                            discFilter.dy = 0;
                            discFilter.blur = 10;
                            discFilter.opacity = 0.5;

                            // Add labels
                            labels[chartId] = {
                                titlerow1: serie.createChild(am4core.Label),
                                titlerow2: serie.createChild(am4core.Label),
                                value: serie.createChild(am4core.Label),
                                percentage: serie.createChild(am4core.Label),
                                divider: topContainer.createChild(am4core.Label)
                            }

                            // split long title
                            var lines = meta[serie.dataFields.category] ? [meta[serie.dataFields.category]] : [''];
                            lines.push('');
                            if (lines[0].length > 19) {
                                var line = lines[0].split(' ');
                                lines[0] = '';
                                var ln = 0;
                                for (let l in line) {
                                    if (lines[ln].length + line[l].length > 19) {
                                        ln++;
                                        lines[ln] = '';
                                    }
                                    if (lines[ln] != '') lines[ln] += ' ';
                                    lines[ln] += line[l];
                                }
                            }
                            if (lines[2] && lines[2].length) lines[1] += '...';
                            // add metadata to data object
                            minfin_data[chartId]['meta']['titlerow'] = lines;
                            // add disc labels
                            var labelNames = new Array('titlerow2', 'titlerow1', 'value', 'percentage');
                            for (let l in labelNames) {
                                var label = labels[chartId][labelNames[l]];
                                switch (labelNames[l]) {
                                    case 'titlerow1':
                                        label.text = lines[0];
                                        label.fontWeight = 'bold';
                                        break;
                                    case 'titlerow2':
                                        label.text = lines[1];
                                        label.fontWeight = 'bold';
                                        break;
                                    case 'value':
                                        var sum = "€ {values.value.sum}";
                                        if (minfin_data[chartId].meta.datatype == 'rijksfinancien_triple' || minfin_data[chartId].meta.datatype == 'rijksfinancien_single') {
                                            sum = minfin_data[chartId].meta.year_totals[minfin_data[chartId].meta.current_year];
                                        }

                                        if (minfin_data[chartId].meta.range) {
                                            sum = minfin_data[chartId].meta.range;
                                        } else if (minfin_data[chartId].meta.legend_total) {
                                            sum = minfin_data[chartId].meta.legend_total;
                                        } else {
                                            sum = format_number(sum, minfin_data[chartId].meta);
                                        }

                                        label.text = sum;
                                        if (minfin_data[chartId].meta.range) label.text = minfin_data[chartId].meta.range;

                                        label.fontWeight = 'bold';
                                        break;
                                    case 'percentage':
                                        if (meta.percentage) {
                                            var perc = minfin_data[chartId].meta.percentage - 100;
                                            perc = perc.toLocaleString('nl-NL', { style: 'decimal', maximumFractionDigits: 2 })
                                            label.text = "Ten opzichte van " + (minfin_api.data.year - 1) + " " + perc + "%";
                                        } else {
                                            label.text = "";
                                        }
                                        label.fontWeight = '400';
                                        break;
                                }


                                label.fontSize = '0';
                                //label.fontFamily = _minfin_charts_options.font;
                                label.align = "center";
                                label.isMeasured = false;
                                label.horizontalCenter = "middle";
                                label.verticalCenter = "middle";
                                label.y = 0;
                                label.id = "disc_label_" + labelNames[l];
                            }
                        } else if (options.divider || options.multiplier) {

                            // Add labels
                            labels[chartId] = {
                                divider: topContainer.createChild(am4core.Label)
                            }
                        }
                        // add divider info
                        if (options.divider || options.multiplier) {
                            var label = labels[chartId]['divider'];
                            var multiplier_text = parseInt(options.multiplier) * parseInt(options.divider);
                            label.text = multiplier_text == 1000000000 ? "Alle bedragen in miljarden" : multiplier_text == 1000000 ? "Alle bedragen in miljoenen" : multiplier_text > 1 ? "Alle bedragen x" + multiplier_text : '';
                            if (csv_date && csv_date != '') {
                                label.text += String.fromCharCode(10) + String.fromCharCode(13) + 'Laatste update ' + csv_date;
                            }
                            label.fontSize = '0';
                            label.fontFamily = _minfin_charts_options.font;
                            label.align = "left";
                            label.verticalCenter = false;
                            label.x = am4core.percent(50);
                            label.y = 0;
                            label.id = "disc_label_divider";
                        }

                        // EVENTS
                        serie.slices.template.events.on("over", function (event) {
                            event.target.setState(event.target.state);
                            show_popup(serie.chartId, event.target._dataItem._index, false, event.target);
                            // cursor
                            serie.slices.each(function (item) {
                                item.cursorOverStyle = am4core.MouseCursorStyle.default;
                                if (item == event.target) {
                                    if (minfin_data[chartId].meta.quicklink && !isTouchDevice()) {
                                        if (item.state != 'inactive' && item._dataItem._dataContext.link) {
                                            if (item._dataItem._dataContext.link.indexOf('ext:') == 0) {
                                                item.cursorOverStyle = am4core.MouseCursorStyle.pointer;
                                            } else if (item._dataItem._dataContext.link.indexOf('internal:') == 0) {
                                                if ((typeof (item._dataItem._dataContext.self_info) != 'undefined' && item._dataItem._dataContext.self_info) || (typeof (item._dataItem._dataContext.child_info) != 'undefined' && item._dataItem._dataContext.child_info)) {
                                                    item.cursorOverStyle = am4core.MouseCursorStyle.pointer;
                                                }
                                            } else {
                                                item.cursorOverStyle = am4core.MouseCursorStyle.pointer;
                                            }
                                        }
                                    }
                                }
                            });
                        });
                        serie.slices.template.events.on("out", function (event) {
                            sluit_popup();
                            setTimeout(function (e) {
                                e.target.setState(event.target.state);
                            }, 20, event);
                        });
                        serie.slices.template.events.on("hit", function (event) {
                            serie.slices.each(function (item) {
                                if (item == event.target) {
                                    if (minfin_data[chartId].meta.quicklink && !isTouchDevice()) {
                                        if (item.state != 'inactive' && item._dataItem._dataContext.link) {
                                            if (item._dataItem._dataContext.link.indexOf('ext:') == 0) {
                                                var link = item._dataItem._dataContext.link;
                                                location.href = link.replace(/ext:/, '');
                                            } else if (item._dataItem._dataContext.link.indexOf('json:') == 0 || item._dataItem._dataContext.link.indexOf('csv:') == 0) {
                                                if ((typeof (item._dataItem._dataContext.self_info) != 'undefined' && item._dataItem._dataContext.self_info) || (typeof (item._dataItem._dataContext.child_info) != 'undefined' && item._dataItem._dataContext.child_info)) {
                                                    var link = item._dataItem._dataContext.link;
                                                    var parts = link.split(':');
                                                    for (var ds in minfin_api['drupal_structure']) {
                                                        if (!minfin_api['path'][minfin_api['drupal_structure'][ds]]) {
                                                            minfin_api['path'][minfin_api['drupal_structure'][ds]] = parts[parts.length - 1];
                                                            reloadnow(serie.chartId);
                                                            break;
                                                        }
                                                    }
                                                }
                                            } else if (item._dataItem._dataContext.link.indexOf('internal:') == 0) {
                                                if ((typeof (item._dataItem._dataContext.self_info) != 'undefined' && item._dataItem._dataContext.self_info) || (typeof (item._dataItem._dataContext.child_info) != 'undefined' && item._dataItem._dataContext.child_info)) {
                                                    var link = item._dataItem._dataContext.link;
                                                    var parts = link.split('/');
                                                    setDrupalPath('identifier', parts[parts.length - 1]);
                                                    reloadnow(serie.chartId);
                                                }
                                            } else {
                                                var url = item._dataItem._dataContext.link.replace(/\/\[([0-9]{4})\]\//, '/' + item._dataItem._dataContext.data_year + '/');
                                                location.href = url + addQuery('graph', '#');
                                            }
                                        }
                                    } else {
                                        mousePos.item = item;
                                        var reset = setSlice(chartId, item._dataItem._index);
                                        if (reset) {
                                            sluit_popup(true, false);
                                        }
                                        else {
                                            open_popup(chartId, item._dataItem._index, item);
                                        }
                                    }
                                }
                            });
                        });

                        break;
                    case 'stacked-column':
                    case 'clustered-column':

                        // create bar chart
                        var animation_duration = 1000;
                        charts[chartId] = container.createChild(am4charts.XYChart);
                        var chart = charts[chartId];
                        chart.domId = id;
                        chart.local = typeof (options.local) != 'undefined' ? options.local : false;
                        chart.bartotals = typeof (options.bartotals) != 'undefined' ? options.bartotals : false;
                        chart.noofbars = typeof (options.bars) != 'undefined' ? options.bars.length : 0;
                        // chart size
                        // chart.padding(0, 10, 0, 10); // not working correctly with single charts
                        // set language
                        chart.language.locale = am4lang_nl_NL;

                        // prefixes
                        chart.numberFormatter.numberFormat = "#a";
                        chart.numberFormatter.bigNumberPrefixes = [
                            { "number": 1e+6, "suffix": "mln" },
                            { "number": 1e+9, "suffix": "mld" },
                            { "number": 1e+12, "suffix": "T" }
                        ];

                        // legend
                        // chart.legend = new am4charts.Legend();
                        // color steps
                        chart.colors.step = 1;
                        // highlight if chart is ready
                        chart.events.on("ready", function (ev) {
                            // hook
                            if (options && options.onready) {
                                try {
                                    executeFunctionByName('ready_' + options.onready, window, data, options);
                                } catch (error) { }
                            }
                            if (typeof (set_manual_indexcolors) == 'function') {
                                set_manual_indexcolors();
                            }
                            if (minfin_api.data.category) preSelect(chartId);
                            // remove spinner
                            document.querySelector('#' + charts[chartId].domId).classList.remove('spin')
                            // show bar/donut switch
                            var carousel = document.querySelector('#' + charts[chartId].domId).parentElement.parentElement;
                            carousel = carousel.querySelector('#chart_nav_carousel');
                            if (carousel && (carousel.classList.contains('bar') || carousel.classList.contains('donut')) && !carousel.classList.contains('noswitch')) {
                                carousel.style.display = 'block';
                                carousel.setAttribute('tabindex', 6);
                                if (carousel.classList.contains('bar')) {
                                    carousel.setAttribute('title', 'Toon staafdiagram');
                                    carousel.setAttribute('aria-label', 'Toon staafdiagram');
                                }
                                if (carousel.classList.contains('donut')) {
                                    carousel.setAttribute('title', 'Toon donut');
                                    carousel.setAttribute('aria-label', 'Toon donut');
                                }
                            }
                            // resize
                            resize_graph(animation_duration);
                            // remove tabindex
                            removeTabIndex();
                            // prepare selectbox
                            if (minfin_data[chartId].options['selectbox']) {
                                setTimeout(function () {
                                    set_manual_bars(minfin_data[chartId].options['target'], 'init');
                                }, 500);
                                var selectbox = minfin_data[chartId].options['selectbox']['id'] ? '#' + minfin_data[chartId].options['selectbox']['id'] : '.manual_selector';
                                $('#' + minfin_data[chartId].options['target'] + '_navigation ' + selectbox).parent().parent().css('display', 'block');
                                $('#' + minfin_data[chartId].options['target'] + '_navigation ' + selectbox).parent().css('display', 'block');
                                $('#' + minfin_data[chartId].options['target'] + '_navigation_top ' + selectbox).parent().parent().css('display', 'block');
                                $('#' + minfin_data[chartId].options['target'] + '_navigation_top ' + selectbox).parent().css('display', 'block');
                            }
                            // prepare sliderbox
                            if (minfin_data[chartId].options['slider']) {
                                var sliderbox = minfin_data[chartId].options['selectbox']['id'] ? '#' + minfin_data[chartId].options['selectbox']['id'] : '.manual_selector';
                                $('#' + minfin_data[chartId].options['target'] + '_navigation ' + sliderbox).parent().parent().css('display', 'block');
                                $('#' + minfin_data[chartId].options['target'] + '_navigation ' + sliderbox).parent().css('display', 'block');
                                $('#' + minfin_data[chartId].options['target'] + '_navigation_top ' + sliderbox).parent().parent().css('display', 'block');
                                $('#' + minfin_data[chartId].options['target'] + '_navigation_top ' + sliderbox).parent().css('display', 'block');
                            }
                            setGraphTop(chartId);
                        });

                        // axis
                        var yearAxis = chart.xAxes.push(new am4charts.CategoryAxis());

                        // base line color
                        yearAxis.renderer.baseGrid.strokeOpacity = 0;

                        yearAxis.dataFields.category = options.category;
                        yearAxis.renderer.minGridDistance = 60;
                        yearAxis.renderer.grid.template.location = 0;
                        yearAxis.interactionsEnabled = false;
                        yearAxis.renderer.grid.template.strokeWidth = 0;

                        yearAxis.renderer.labels.template.wrap = true;
                        if (options.truncate) {
                            yearAxis.renderer.labels.template.truncate = true;
                        }
                        yearAxis.renderer.labels.template.maxWidth = 240;
                        yearAxis.events.on("sizechanged", function (ev) {
                            let axis = ev.target;
                            let cellWidth = axis.pixelWidth / (axis.endIndex - axis.startIndex);
                            axis.renderer.labels.template.maxWidth = cellWidth + 20;
                        });

                        if (typeof (options.xaxis) == 'undefined' || options.xaxis) {
                            yearAxis.renderer.labels.template.disabled = false;
                        } else {
                            yearAxis.renderer.labels.template.disabled = true;
                        }

                        // stacked or clustered
                        if (type == 'clustered-column') {
                            yearAxis.renderer.cellStartLocation = 0.15;
                            yearAxis.renderer.cellEndLocation = 0.85;
                        }

                        var valueAxis = chart.yAxes.push(new am4charts.ValueAxis());

                        // base line color
                        valueAxis.renderer.baseGrid.strokeOpacity = 0.5;
                        valueAxis.renderer.baseGrid.strokeWidth = 1;
                        valueAxis.renderer.baseGrid.stroke = am4core.color("#0F6999");

                        valueAxis.tooltip.disabled = true;
                        valueAxis.renderer.grid.template.strokeOpacity = 0.05;
                        valueAxis.renderer.minGridDistance = 20;
                        valueAxis.interactionsEnabled = false;
                        valueAxis.min = 30;
                        valueAxis.adjustLabelPrecision = false;

                        valueAxis.renderer.grid.template.strokeOpacity = 1;
                        valueAxis.renderer.grid.template.stroke = am4core.color("#E5F0F9");
                        valueAxis.renderer.grid.template.strokeWidth = 1;
                        valueAxis.renderer.axisFills.template.disabled = false;
                        valueAxis.renderer.axisFills.template.fill = am4core.color("white");
                        valueAxis.renderer.axisFills.template.fillOpacity = 1;
                        valueAxis.renderer.minGridDistance = 40;
                        valueAxis.fillRule = function (dataItem) {
                            dataItem.axisFill.visible = true;
                        }

                        valueAxis.min = 0;
                        // show values below zero
                        if (typeof (options.lowest) != 'undefined' && options.lowest < 0) {
                            valueAxis.min = options.lowest;
                            valueAxis.max = options.highest;
                        }

                        // no labels
                        if (typeof (options.yaxis) == 'undefined' || options.yaxis) {
                            valueAxis.renderer.labels.template.disabled = false;
                            valueAxis.renderer.minWidth = 35;
                            if (parseInt(options.yaxis) > 0) {
                                valueAxis.renderer.minGridDistance = parseInt(options.yaxis);
                            }
                        } else {
                            valueAxis.renderer.labels.template.disabled = true;
                            valueAxis.renderer.minWidth = 0;
                        }


                        //var label = categoryAxis.renderer.labels.template;
                        //label.wrap = true;
                        //label.maxWidth = 120;


                        // list of values determines number of bars
                        options.bars = typeof (options.bars) == 'object' ? options.bars : [options.bars];
                        options.value = typeof (options.value) != 'undefined' ? options.value : "value";

                        // container for overlay info
                        var topContainer = chart.chartContainer.createChild(am4core.Container);
                        topContainer.layout = "absolute";
                        topContainer.toBack();
                        topContainer.paddingBottom = 15;
                        topContainer.width = am4core.percent(100);

                        // Add labels
                        labels[chartId] = {
                            divider: topContainer.createChild(am4core.Label),
                            bar_totals: [],
                            segment_totals: [],
                        }
                        // add divider info
                        if (options.divider || options.multiplier) {
                            var label = labels[chartId]['divider'];


                            var multiplier_text = parseInt(options.multiplier) * parseInt(options.divider);
                            label.text = '';
                            //chartid
                            if (typeof (csv_options.small_title) == 'string') {
                                label.text = csv_options.small_title + String.fromCharCode(10) + String.fromCharCode(13);
                            }
                            label.text += multiplier_text == 1000000000 ? "Alle bedragen in miljarden" : multiplier_text == 1000000 ? "Alle bedragen in miljoenen" : multiplier_text > 1 ? "Alle bedragen x" + multiplier_text : '';
                            if (typeof (csv_options.note) == 'string') {
                                label.text += String.fromCharCode(10) + String.fromCharCode(13) + csv_options.note.replace('\r', String.fromCharCode(10) + String.fromCharCode(13));
                            }
                            if (csv_date && csv_date != '') {
                                label.text += String.fromCharCode(10) + String.fromCharCode(13) + 'Laatste update ' + csv_date;
                            }
                            label.fontSize = '0';
                            //label.fontFamily = _minfin_charts_options.font;
                            label.x = 0;
                            label.y = -80;
                            label.id = "disc_label_divider";
                        }

                        // sortVal[1] = 'reversed';
                        // sortVal[0] = options.value;
                        // data.sort(sortArray);
                        chart.reversedLegend = true;

                        var smooth = false; // patterns cannot animate smooth
                        for (let d in data) {
                            if (data[d].extra && data[d].extra.indexOf('pattern') > -1) smooth = false; // there is a pattern in the current dataset
                        }

                        for (let d in data) {
                            // prepare series
                            if (!options.series || options.series.indexOf(data[d]['identifier']) > -1) {
                                series[series.length] = chart.series.push(new am4charts.ColumnSeries());
                                var serie = series[series.length - 1];

                                //serie.background.fill = am4core.color('lime');

                                // map data to series
                                serie.dataFields.categoryX = typeof (options.category) == 'string' ? options.category : "category";
                                serie.category = serie.dataFields.categoryX;
                                serie.dataFields.valueY = data[d][serie.dataFields.categoryX];
                                // divider
                                if (options.divider) {
                                    for (let b in options.bars) {
                                        if (data[d][options.bars[b]]) {
                                            data[d][options.bars[b]] = divide_number(data[d][options.bars[b]], options);
                                        }
                                    }
                                }
                                // prevent null values
                                // if (!data[d].value) {
                                //     data[d].value = data[d][options.value];
                                // }
                                data[d].value = data[d][options.value];

                                //var serie = chart.series.push(new am4charts.ColumnSeries());
                                var barwidth = (csv_options && csv_options.bar_size) ? csv_options.bar_size : 0.4;
                                if (barwidth <= 1) {
                                    serie.columns.template.width = am4core.percent(Math.round(barwidth * 100));
                                } else {
                                    serie.columns.template.width = barwidth;
                                }
                                // stacked or clustered
                                if (type == 'clustered-column') {
                                    serie.columns.template.width = am4core.percent(Math.round(90));
                                }

                                barwidth = Math.round(barwidth * 100);
                                serie.columns.template.tooltipText = "";
                                // set some option vars
                                serie.name = data[d][serie.dataFields.categoryX];
                                serie.chartId = chartId;
                                serie.legendId = d;
                                serie.uniqueGroup = data[d].group;
                                serie.extra = data[d].extra;
                                serie.barId = options.value;
                                serie.barName = typeof (options.name) != 'undefined' ? options.name : false;
                                serie.properties.chartId = chartId;
                                serie.singlebars = typeof (options.singlebars) != 'undefined' ? options.singlebars : false;
                                serie.nohighlight = typeof (options.nohighlight) != 'undefined' ? options.nohighlight : false;

                                // stacked or clustered
                                serie.stacked = type == 'stacked-column' ? true : false;

                                // animation (1000)
                                serie.defaultState.transitionDuration = animation_duration;
                                serie.defaultState.transitionEasing = am4core.ease.linear;
                                serie.sequencedInterpolation = false;

                                // set stroke
                                serie.columns.template.stroke = _minfin_charts_options.stroke_color;
                                serie.columns.template.strokeWidth = _minfin_charts_options.stroke_width;
                                serie.columns.template.strokeOpacity = 1;
                                serie.columns.template.fillOpacity = 1;

                                // cursor
                                serie.columns.template.cursorOverStyle = am4core.MouseCursorStyle.default;

                                serie.columns.template.adapter.add("fill", function (fill, target) {
                                    if (fill && target && target.sprite && target.sprite._dataItem && target.sprite._dataItem.component && target.sprite._dataItem.component.extra) {
                                        if (target.sprite._dataItem.component.extra.indexOf('pattern') > -1) { // pattern
                                            var col = fill._className == 'Pattern' ? fill.properties.fill : fill;
                                            col = col._value.r + '' + col._value.g + '' + col._value.b;
                                            if (target.sprite._dataItem.categories.categoryX != target.sprite._dataItem.component.barId) {
                                                col += 'l';
                                            }

                                            var pattern = new am4core.RectPattern();
                                            pattern.width = 15
                                            pattern.height = 22;
                                            pattern.rectWidth = 0;
                                            pattern.rectHeight = 0;

                                            var image = new am4core.Image();
                                            image.href = "data:image/svg+xml;charset=utf-8;base64," + minfin_patterns[col];
                                            image.width = 13;
                                            image.height = 20;
                                            pattern.addElement(image.element);

                                            return pattern;
                                        }
                                    }
                                    if (target && target.sprite && target.sprite._dataItem && target.sprite._dataItem.categories.categoryX != target.sprite._dataItem.component.barId) {
                                        return fill.lighten(_minfin_charts_options.dimmed_opacity);
                                    } else {
                                        return fill;
                                    }
                                });

                                if (!smooth) {
                                    // Create default state to colour the pattern
                                    var defaultState = serie.columns.template.states.create("default");
                                    defaultState.adapter.add("fill", function (fill, target) {
                                        var reset = target.sprite._dataItem.component.reset;
                                        if (typeof (reset) == 'undefined') {
                                            reset = true;
                                        }
                                        if (fill && target && target.sprite && target.sprite._dataItem && target.sprite._dataItem.component && target.sprite._dataItem.component.extra) {
                                            if (target.sprite._dataItem.component.extra.indexOf('pattern') > -1) {
                                                var col = fill._className == 'Pattern' ? fill.properties.fill : fill;
                                                col = col._value.r + '' + col._value.g + '' + col._value.b;

                                                if (reset && target.sprite._dataItem.categories.categoryX != target.sprite._dataItem.component.barId && !target.sprite._dataItem.component.nohighlight) {
                                                    col += 'l';
                                                }

                                                var pattern = new am4core.RectPattern();
                                                pattern.width = 15
                                                pattern.height = 15;
                                                pattern.rectWidth = 0;
                                                pattern.rectHeight = 0;

                                                var image = new am4core.Image();
                                                image.href = "data:image/svg+xml;charset=utf-8;base64," + minfin_patterns[col];
                                                image.width = 15;
                                                image.height = 15;
                                                pattern.addElement(image.element);

                                                return pattern;
                                            }
                                        }
                                        if (reset && target && target.sprite && target.sprite._dataItem && target.sprite._dataItem.categories.categoryX != target.sprite._dataItem.component.barId && !target.sprite._dataItem.component.singlebars && !target.sprite._dataItem.component.nohighlight) {
                                            return fill.lighten(_minfin_charts_options.dimmed_opacity);
                                        } else {
                                            return fill;
                                        }
                                    });
                                    defaultState.transitionDuration = 1;
                                }

                                // Create default active state
                                var activeState = serie.columns.template.states.create("active");
                                activeState.properties.shiftRadius = 0;
                                activeState.properties.scale = 1;
                                activeState.adapter.add("fill", function (fill, target) {
                                    return fill;
                                });
                                if (!smooth) {
                                    activeState.transitionDuration = 1;
                                }

                                // Create inactive state
                                var inactiveState = serie.columns.template.states.create("inactive");
                                inactiveState.properties.fill = _minfin_charts_options.inactive_color;
                                inactiveState.properties.fillOpacity = 1;
                                inactiveState.properties.shiftRadius = 0;
                                inactiveState.properties.hoverable = false;
                                inactiveState.properties.clickable = true;
                                inactiveState.properties.cursorOverStyle = am4core.MouseCursorStyle.default;
                                if (!smooth) {
                                    inactiveState.transitionDuration = 1;
                                }

                                // Create hover state
                                var hoverState = serie.columns.template.states.create("hover");
                                hoverState.properties.scale = 1;
                                hoverState.properties.shiftRadius = 0;
                                hoverState.adapter.add("fill", function (fill, target) {
                                    return target.sprite.state == 'inactive' ? _minfin_charts_options.inactive_color : fill;
                                });
                                if (!smooth) {
                                    hoverState.transitionDuration = 1;
                                }

                                // EVENTS
                                serie.columns.template.events.on("over", function (event) {
                                    event.target.setState(event.target.state);
                                    show_popup(serie.chartId, event.target._dataItem.component.legendId, false, event.target);
                                    // cursor
                                    var state = event.target.state
                                    var item = event.target;
                                    item.cursorOverStyle = am4core.MouseCursorStyle.default;
                                    if (minfin_data[chartId].meta.quicklink && !isTouchDevice()) {
                                        if (state != 'inactive') {
                                            var index = event.target._dataItem.component.legendId;
                                            var link = minfin_data[chartId].data[index].link;
                                            if (link && link.indexOf('ext:') == 0) {
                                                item.cursorOverStyle = am4core.MouseCursorStyle.pointer;
                                            } else if (link && link.indexOf('internal:') == 0) {
                                                if ((typeof (minfin_data[chartId].data[d]['self_info']) != 'undefined' && minfin_data[chartId].data[d]['self_info']) || (typeof (minfin_data[chartId].data[d]['child_info']) != 'undefined' && minfin_data[chartId].data[d]['child_info'])) {
                                                    item.cursorOverStyle = am4core.MouseCursorStyle.pointer;
                                                }
                                            } else if (link) {
                                                item.cursorOverStyle = am4core.MouseCursorStyle.pointer;
                                            }
                                        }
                                    }
                                });
                                serie.columns.template.events.on("out", function (event) {
                                    sluit_popup();
                                    setTimeout(function (e) {
                                        e.target.setState(event.target.state);
                                    }, 20, event);
                                });
                                serie.columns.template.events.on("hit", function (event) {
                                    mousePos.item = event.target;
                                    var state = event.target.state
                                    var reset = setSlice(chartId, event.target._dataItem.component.legendId, null, null, event.target._dataItem.component.uniqueGroup);
                                    if (reset) {
                                        sluit_popup(true, false);
                                    }
                                    else {
                                        var item = event.target;
                                        if (minfin_data[chartId].meta.quicklink && !isTouchDevice()) {
                                            if (state != 'inactive') {
                                                var index = event.target._dataItem.component.legendId;
                                                var link = minfin_data[chartId].data[index].link;
                                                if (link && link.indexOf('ext:') == 0) {
                                                    location.href = link.replace(/ext:/, '');
                                                } else if (link && (link.indexOf('json:') == 0 || link.indexOf('csv:') == 0)) {
                                                    if ((typeof (minfin_data[chartId].data[d]['self_info']) != 'undefined' && minfin_data[chartId].data[d]['self_info']) || (typeof (minfin_data[chartId].data[d]['child_info']) != 'undefined' && minfin_data[chartId].data[d]['child_info'])) {
                                                        var parts = link.split(':');
                                                        for (var ds in minfin_api['drupal_structure']) {
                                                            if (!minfin_api['path'][minfin_api['drupal_structure'][ds]]) {
                                                                minfin_api['path'][minfin_api['drupal_structure'][ds]] = parts[parts.length - 1];
                                                                reloadnow(serie.chartId);
                                                                break;
                                                            }
                                                        }
                                                    }
                                                } else if (link && link.indexOf('internal:') == 0) {
                                                    if ((typeof (minfin_data[chartId].data[d]['self_info']) != 'undefined' && minfin_data[chartId].data[d]['self_info']) || (typeof (minfin_data[chartId].data[d]['child_info']) != 'undefined' && minfin_data[chartId].data[d]['child_info'])) {
                                                        var parts = link.split('/');
                                                        setDrupalPath('identifier', parts[parts.length - 1]);
                                                        reloadnow(serie.chartId);
                                                    }
                                                } else if (link) {
                                                    var url = link.replace(/\/\[([0-9]{4})\]\//, '/' + item._dataItem._dataContext.datavalue + '/');
                                                    location.href = url + addQuery('graph', '#');
                                                }
                                            }
                                        } else {
                                            open_popup(chartId, event.target._dataItem.component.legendId, event.target);
                                        }
                                    }
                                });
                            }
                        }

                        // reorder data to bar structure
                        chart.data_ = data;
                        var data_ = [];
                        var key = serie ? serie.dataFields.categoryX : '';

                        // reorder data to bar structure
                        if (options.bars) {
                            for (let v in options.bars) {
                                var obj = {}
                                obj['datavalue'] = '' + options.bars[v];
                                obj[key] = options.name ? options.name : '' + options.bars[v];
                                for (let d in data) {
                                    for (let dc in data[d]) {
                                        var subkey = data[d][key];
                                        if (dc == options.bars[v]) {
                                            obj[subkey] = data[d][dc];
                                        }
                                    }
                                }
                                data_.push(obj);
                            }
                        }

                        // draw extra scale
                        if (typeof (options.axis) != 'undefined' && typeof (options.axis.min) != 'undefined' && typeof (options.axis.max) != 'undefined') {
                            var color = get_minfin_color(typeof (options.axis.color) != 'undefined' ? options.axis.color : 'black');
                            var extraValueAxis = chart.yAxes.push(new am4charts.ValueAxis());
                            extraValueAxis.renderer.opposite = true;
                            extraValueAxis.min = options.axis.min;
                            extraValueAxis.max = options.axis.max;
                            extraValueAxis.strictMinMax = true;
                            extraValueAxis.renderer.grid.template.disabled = true;
                            extraValueAxis.numberFormatter = new am4core.NumberFormatter();
                            extraValueAxis.numberFormatter.numberFormat = "#"
                            extraValueAxis.cursorTooltipEnabled = false;
                            extraValueAxis.renderer.labels.template.fill = am4core.color(color);
                        }

                        // add extra line data
                        var extraSeries = [];
                        if (typeof (options.linedata) == 'object') {
                            for (var ld in options.linedata) {
                                var linecolor = get_minfin_color(options.linedata[ld]['color']);
                                var linetype = typeof (options.linedata[ld]['type']) != 'undefined' ? options.linedata[ld]['type'] : 'line';
                                var linedatakey = typeof (options.linedata[ld]['key']) != 'undefined' ? options.linedata[ld]['key'] : false;
                                if (linedatakey) {
                                    extraSeries[extraSeries.length] = chart.series.push(new am4charts.LineSeries());
                                    var sn = extraSeries.length - 1;
                                    extraSeries[sn].dataFields.valueY = "linedata_" + sn;
                                    extraSeries[sn].dataFields.categoryX = options.category;
                                    extraSeries[sn].yAxis = typeof (options.linedata[ld]['axis']) != 'undefined' && options.linedata[ld]['axis'] ? extraValueAxis : valueAxis;
                                    extraSeries[sn].strokeWidth = 2;
                                    extraSeries[sn].stroke = am4core.color(linecolor);
                                    if (linetype == 'bullet') {
                                        extraSeries[sn].bullets.push(new am4charts.CircleBullet());
                                        extraSeries[sn].fill = am4core.color(linecolor);
                                    }
                                    if (linetype == 'dash') {
                                        extraSeries[sn].strokeDasharray = "4,4";
                                    }
                                    //extraSeries[sn].tooltipText = "pareto: {valueY.formatNumber('#.0')}%[/]";
                                    //extraSeries.strokeOpacity = 0.5;
                                    if (typeof (options.singlebars) != 'undefined' && options.singlebars && !options.linedata[ld]['single']) {
                                        for (var d in data) {
                                            if (typeof (data[d][linedatakey]) != 'undefined' && !isNaN(parseFloat(data[d][linedatakey]))) {
                                                data_[d]['linedata_' + sn] = data[d][linedatakey];
                                            }
                                        }
                                    } else {
                                        var valuechecked = [];
                                        for (var d in data) {
                                            if (typeof (data[d][linedatakey]) != 'undefined' && !isNaN(parseFloat(data[d][linedatakey]))) {
                                                if (!valuechecked[data[d][linedatakey]]) {
                                                    valuechecked[data[d][linedatakey]] = true;
                                                    // add range
                                                     let range = typeof (options.linedata[ld]['axis']) != 'undefined' && options.linedata[ld]['axis'] ? extraValueAxis.axisRanges.create() : valueAxis.axisRanges.create();
                                                    //TODO divide linedata if it's a value onload, not here and not in construct_legend!!
                                                    range.value = typeof (options.linedata[ld]['axis']) != 'undefined' && options.linedata[ld]['axis'] ? data[0][linedatakey] : divide_number(data[d][linedatakey], options);
                                                    range.grid.stroke = am4core.color(linecolor);
                                                    range.grid.strokeWidth = 2;
                                                    if (linetype == 'dash') {
                                                        range.grid.strokeDasharray = "4,4";
                                                    }
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        }

                        chart.data = data_;

                        // add top label
                        if (options.bartotals && chart.noofbars) {
                            for (let b = 0; b < chart.noofbars; b++) {
                                if (series[series.length - 1]) {
                                    labels[chartId].bar_totals.push(series[series.length - 1].createChild(am4core.Label));
                                    var label = labels[chartId].bar_totals[labels[chartId].bar_totals.length - 1];
                                    label.text = '';
                                    label.wait = false;
                                    label.fontSize = 0;
                                    label.isMeasured = false;
                                    label.horizontalCenter = "middle";
                                    label.verticalCenter = "bottom";
                                    label.x = am4core.percent((parseInt(b) * (100 / chart.noofbars)) + (0.5 * (100 / chart.noofbars)));
                                }
                            }
                        }

                        break;
                }

                // create legend
                if (charts[chartId] && typeof (options.legend) != 'undefined') {
                    construct_legend(chartId);
                }
            }

            /*
            // get color from index if available
            */
            function get_minfin_color(key) {
                key = typeof (key) != 'undefined' ? key : 'black';
                return typeof (minfin_color_names[key]) != 'undefined' ? minfin_color_names[key] : key;
            }

            /*
            // remove tab index
            */
            function removeTabIndex() {
                $('.amcharts-Sprite-group.amcharts-Container-group').each(function () {
                    $(this).attr('tabindex', '-1');
                });
            }

            /*
            // numbers divider
            */
            function divide_number(number, options) {
                var divider = options.divider ? options.divider : 1;
                if (options.round == 'floor') {
                    number = Math.floor(number / divider);
                } else if (options.round == 'ceil') {
                    number = Math.ceil(number / divider);
                } else if (options.round == 'round') {
                    number = Math.round(number / divider);
                } else {
                    number = number / divider;
                }
                return number;
            }

            /*
            // round numbers
            */
            function round_number(number, options) {
                var decimals = options.decimals ? options.decimals : 0;
                decimals = Math.pow(10, decimals);
                if (number == '') {
                    return number;
                } else if (decimals > 1) {
                    number = Math.round(number * decimals) / decimals;
                } else if (options.round == 'floor') {
                    number = Math.floor(number);
                } else if (options.round == 'ceil') {
                    number = Math.ceil(number);
                } else if (options.round == 'round') {
                    number = Math.round(number);
                }
                return number;
            }

            /*
            // format numbers
            */
            function format_number(number, options) {
                if (options.negatives) {
                    var testnum = '' + number;
                    if (testnum.indexOf('€ {') == 0) {
                        number = testnum.replace(/€ {/, '€ -{');
                    } else {
                        number = parseFloat(number) * -1;
                    }
                }
                if (options.currency && options.currency == 'EUR') {
                    var style = options.currency ? 'currency' : 'decimal';
                    var currency = options.currency ? options.currency : 'EUR';
                    var decimals = options.decimals ? options.decimals : 0;
                    return number.toLocaleString('nl-NL', { style: style, currency: currency, minimumFractionDigits: decimals });
                } else {
                    var decimals = options.decimals ? options.decimals : 0;
                    var currency = options.currency ? ' ' + options.currency : '';
                    return number.toLocaleString('nl-NL', { style: 'decimal', minimumFractionDigits: decimals }) + currency;
                }
            }

            /*
            // split ranges into numbers
            */
            function split_range(range, options) {
                if (range && range != '') {
                    range = range.match(/([0-9]+)/g);
                    if (range.length == 2) {
                        return format_number(parseFloat(range[0]), options) + ' - ' + format_number(parseFloat(range[1]), options);
                    }
                }
                return range;
            }

            /*
            // preselect slices from query string
            */
            function preSelect(chartId) {
                var category = minfin_api.data.category
                if (category) {
                    var index = 0;
                    var cat = -1;

                    // seek category per chart
                    // for (let s in series) {
                    //     console.log(series[s])
                    //     if (series[s].chartId == chartId) {
                    //         cat = series[s].dataFields.category ? series[s].dataFields.category : series[s].dataFields.categoryX;
                    //     }
                    // }

                    // seek chart item with category
                    for (let o in minfin_data[chartId].data) {
                        if (minfin_data[chartId].data[o].identifier == category) {
                            //if (minfin_data[chartId].data[o][cat] == category) {
                            setTimeout(function (index, chartId) {
                                // Set default
                                setSlice(chartId, index, true, true);
                            }, 1, index, chartId);
                        }
                        index++;
                    }
                }
            }

            /*
            // get percentage compared to last year
            */
            function getPerc(chart, index) {
                for (let m in minfin_api.map) {
                    var phase = minfin_api.map[minfin_api.data.phase] ? minfin_api.map[minfin_api.data.phase] : minfin_api.data.phase;
                    if (minfin_api.map[m] == phase) {
                        // TODO titel nog aanpassen
                        if (minfin_data[chart].meta.autoyear) {
                            minfin_data[chart].data[index].data_phase = cap(m);
                        } else {
                            minfin_data[chart].data[index].data_phase = minfin_data[chart].meta.title;
                        }
                        var thisyear = minfin_data[chart].data[index][minfin_data[chart].data[index].data_year];
                        var pastyear = minfin_data[chart].data[index][parseInt(minfin_data[chart].data[index].data_year - 1)];
                        var tov = '';
                        if (pastyear && thisyear) {
                            var perc = thisyear / pastyear * 100 - 100;
                            tov = 'Ten opzichte van ' + parseInt(minfin_data[chart].data[index].data_year - 1) + ' ' + perc.toLocaleString('nl-NL', { style: 'decimal', maximumFractionDigits: 2 }) + '%';
                        }
                        minfin_data[chart].data[index].data_tov = tov;
                        break;
                    }
                }
            }

            /*
            // create legend
            */
            var tabindexcounter = 10;
            function construct_legend(chart) {
                var target = document.getElementById(charts[chart].domId + "_legend");
                var i = 0;

                var templateId = '';
                if (minfin_data[chart].options && minfin_data[chart].options['script_template_id'] && minfin_data[chart].options['script_template_id'] != '') {
                    templateId = '_' + minfin_data[chart].options['script_template_id'];
                }

                if (minfin_data[chart].meta.legend && target) {

                    // add header to legend template
                    var temp = document.getElementById("chart_legend_header_template" + templateId);
                    if (temp) {
                        var node = mustache(temp.innerHTML, minfin_data[chart].meta);
                        target.appendChild(node);
                    }
                    if (minfin_data[chart].meta.legend === true || typeof (minfin_data[chart].meta['singlebars']) == 'undefined' || !minfin_data[chart].meta['singlebars']) {

                        var beforenode = document.createElement("div");
                        beforenode.classList.add('chart_legend_data_before');
                        target.appendChild(beforenode);
                        var containernode = document.createElement("div");
                        if (minfin_data[chart].options.legend_limit) {
                            containernode.classList.add('chart_legend_data_container');
                            containernode.addEventListener('scroll', function (e) {
                                if ($(this).scrollTop() + $(this).innerHeight() > $(this)[0].scrollHeight - 5) {
                                    // end
                                    $(this).parent().find('.chart_legend_data_after').removeClass('show');
                                } else {
                                    $(this).parent().find('.chart_legend_data_after').addClass('show');

                                }
                                if ($(this).scrollTop() < 5) {
                                    // start
                                    $(this).parent().find('.chart_legend_data_before').removeClass('show');
                                } else {
                                    $(this).parent().find('.chart_legend_data_before').addClass('show');
                                }
                                if ($(this).closest('.container').find('#container-links').length) {
                                    $(this).css('max-height', ($(this).closest('.container').find('#container-links').height() - $(this).parent().find('.chart_legend_header').height() - 31));
                                }
                            });
                        }
                        target.appendChild(containernode);
                        if (minfin_data[chart].options.legend_limit) {
                            if ($(containernode).closest('.container').find('#container-links').length) {
                                $(containernode).css('max-height', ($(containernode).closest('.container').find('#container-links').height() - $(containernode).parent().find('.chart_legend_header').height() - 31));
                            }
                        }
                        var afternode = document.createElement("div");
                        afternode.classList.add('chart_legend_data_after');
                        target.appendChild(afternode);
                        target = containernode;
                        var extraindex = 0;

                        for (let d in minfin_data[chart].data) {

                            var singebars = typeof (minfin_data[chart].meta.singlebars) && minfin_data[chart].meta.singlebars ? true : false;

                            if (singebars && typeof (minfin_data[chart].data[d]['title']) != 'undefined' && typeof (minfin_data[chart].data[d][minfin_data[chart].data[d]['title']]) != 'undefined') {
                                minfin_data[chart].data[d]['value'] = minfin_data[chart].data[d][minfin_data[chart].data[d]['title']];
                            } else if (singebars && typeof (minfin_data[chart].data[d]['identifier']) != 'undefined' && typeof (minfin_data[chart].data[d][minfin_data[chart].data[d]['identifier']]) != 'undefined') {
                                minfin_data[chart].data[d]['value'] = minfin_data[chart].data[d][minfin_data[chart].data[d]['identifier']];
                            }
                            //console.log(minfin_data[chart].data[d])
                            //for (let d = minfin_data[chart].data.length - 1; d >= 0; d--) {

                            if (series[chart] && series[chart].dataItem._className == 'ColumnSeriesDataItem') {
                                minfin_data[chart].data[d].data_year = series[chart].dataItem.component.barId; // 'YO'
                            }
                            getPerc(chart, d);

                            temp = document.getElementById("chart_legend_template" + templateId).innerHTML;
                            node = mustache(temp, minfin_data[chart].data[d], i, chart);
                            node.setAttribute('data_id', node.getAttribute('id'));
                            node.setAttribute('id', 'chart' + chart + '_' + node.getAttribute('id').replace(/\W/g, '_'));
                            if (typeof (minfin_data[chart].data[d]['group']) != 'undefined') {
                                node.setAttribute('group', minfin_data[chart].data[d]['group']);
                            }
                            if (minfin_data[chart].meta.singlebars || (typeof (minfin_data[chart].data[d]['curyear']) != 'undefined' && minfin_data[chart].data[d]['curyear'])) {
                                node.setAttribute('curyear', 'yes');
                            } else {
                                node.setAttribute('curyear', 'no');
                            }

                            // add manualinfo about patterns
                            if (minfin_data[chart].data[d].extra && minfin_data[chart].data[d].extra.indexOf('pattern') > -1 && typeof (minfin_data[chart].data[d]['group']) != 'undefined') {
                                var txt = node.querySelectorAll(".chart_legend_sub_data .description");
                                if (txt.length) {
                                    txt = txt[0].innerHTML;
                                    if (txt != '') {
                                        txt += '<br/><br/>';
                                    } else {
                                        txt += '<br/>';
                                    }
                                    txt += patternTxt;
                                    node.querySelectorAll(".chart_legend_sub_data .description")[0].innerHTML = txt;
                                }
                            }

                            if (minfin_data[chart].data[d]['no_card']) {
                                if (node.querySelector('.chart_legend_post_data')) {
                                    node.querySelector('.chart_legend_post_data').style.display = 'none';
                                }
                            }

                            if (minfin_data[chart].data[d]['noindex']) {
                                node.style.display = 'none';
                            }

                            target.appendChild(node);

                            var colorObj = document.querySelector("#" + node.getAttribute('id') + " .chart_legend_item_color");
                            var colorIndex = i % chart_colors.length;
                            var color = chart_colors[colorIndex]._value;

                            if (!singebars && (typeof (minfin_data[chart].data[d]['curyear']) == 'undefined' || !minfin_data[chart].data[d]['curyear'])) {
                                colorObj.style.backgroundColor = 'rgb(' + parseInt(color.r + (255 - color.r) / 2) + ',' + parseInt(color.g + (255 - color.g) / 2) + ',' + parseInt(color.b + (255 - color.b) / 2) + ')';
                            } else {
                                colorObj.style.backgroundColor = 'rgb(' + color.r + ',' + color.g + ',' + color.b + ')';
                            }
                            colorObj.setAttribute('maincol', 'rgb(' + color.r + ',' + color.g + ',' + color.b + ')');

                            if (minfin_data[chart].data[d].extra) {
                                if (minfin_data[chart].data[d].extra.indexOf('pattern') > -1) {
                                    var col = color.r + '' + color.g + '' + color.b;
                                    if (typeof (minfin_data[chart].data[d]['curyear']) == 'undefined' || !minfin_data[chart].data[d]['curyear']) {
                                        col += 'l';
                                    }
                                    colorObj.style.backgroundImage = "url('data:image/svg+xml;charset=utf-8;base64," + minfin_patterns[col] + "')";
                                    colorObj.style.backgroundSize = '15px 15px';
                                    colorObj.style.backgroundColor = '';
                                    //colorObj.style.backgroundImage = 'linear-gradient(transparent 0%, transparent 75%, rgb(229,240,249) 75%, rgb(229,240,249) 100%)';
                                    //colorObj.style.backgroundSize = '16px 4px';
                                }
                            }

                            i++;

                            var clickNode = node.getElementsByClassName("chart_legend_item_header")[0];
                            clickNode.setAttribute('tabindex', tabindexcounter);
                            tabindexcounter++;
                            clickNode.addEventListener('focus', function (e) {
                                // use focus instead of click for keyboard purpose
                                sluit_popup(-2, false);
                                var obj = e.target;
                                var group = obj.parentNode.getAttribute('group');
                                var counter = 0;
                                while (counter < 32 && !obj.classList.contains('chart_legend')) {
                                    obj = obj.parentNode;
                                    counter++;
                                }
                                var id = obj.id.replace(/_legend$/, '');
                                var chart = document.getElementById(id).getAttribute('chartId');
                                setSlice(chart, e.target.parentNode.attributes.index.value, null, null, group, true);
                            });
                            clickNode.addEventListener('click', function (e) {
                                // blur after click so that another click refocusses to close again
                                setTimeout(function (obj) {
                                    obj.target.blur();
                                }, 1, e);
                            });

                            var link = document.querySelectorAll("#" + node.getAttribute('id') + " .minfin_link");
                            var legendlink = false;
                            if (link.length) {
                                for (let l = 0; l < link.length; l++) {
                                    if (link[l].getAttribute('minfin-link') != null) {
                                        legendlink = link[l].getAttribute('minfin-link');
                                        if (link[l].getAttribute('minfin-link') && link[l].getAttribute('minfin-link') != '') {
                                            // we have a link, thus an identifier
                                            // do we have children?
                                            //console.log(link[l].getAttribute('minfin-link'))
                                            if (link[l].getAttribute('minfin-link').indexOf('execute:') == 0) {
                                                link[l].addEventListener('click', function (e) {
                                                    var parts = this.getAttribute('minfin-link').replace(/execute:/, '').split('|');
                                                    var func = parts[0];
                                                    parts.shift();
                                                    parts.push(chart);
                                                    executeFunctionByName(func, window, parts);
                                                });
                                            } else if (link[l].getAttribute('minfin-link').indexOf('ext:') == 0) {
                                                var url = link[l].getAttribute('minfin-link').replace(/ext:/, '');
                                                link[l].setAttribute('minfin-link', url);
                                                link[l].setAttribute('tabindex', tabindexcounter);
                                                tabindexcounter++;
                                                link[l].addEventListener('click', function (e) {
                                                    var url = this.getAttribute('minfin-link');
                                                    location.href = url; // + addQuery('graph', '#');
                                                });
                                            } else if (link[l].getAttribute('minfin-link').indexOf('internal:') == 0) {
                                                if ((typeof (minfin_data[chart].data[d]['self_info']) != 'undefined' && minfin_data[chart].data[d]['self_info']) || (typeof (minfin_data[chart].data[d]['child_info']) != 'undefined' && minfin_data[chart].data[d]['child_info'])) {
                                                    link[l].setAttribute('tabindex', tabindexcounter);
                                                    tabindexcounter++;
                                                    link[l].addEventListener('click', function (e) {
                                                        var parts = this.getAttribute('minfin-link').split('/');
                                                        setDrupalPath('identifier', parts[parts.length - 1]);
                                                        reloadnow(chart);
                                                    });
                                                } else {
                                                    link[l].style.display = 'none';
                                                }
                                            } else if (link[l].getAttribute('minfin-link').indexOf('json:') == 0 || link[l].getAttribute('minfin-link').indexOf('csv:') == 0) {
                                                var url = link[l].getAttribute('minfin-link').replace(/\/\[([0-9]{4})\]\//, '/' + minfin_data[chart].data[d].data_year + '/');
                                                link[l].setAttribute('minfin-link', url);
                                                link[l].setAttribute('tabindex', tabindexcounter);
                                                tabindexcounter++;
                                                link[l].addEventListener('click', function (e) {
                                                    for (var ds in minfin_api['drupal_structure']) {
                                                        if (!minfin_api['path'][minfin_api['drupal_structure'][ds]]) {
                                                            minfin_api['path'][minfin_api['drupal_structure'][ds]] = minfin_api['select'];
                                                            reloadnow(chart);
                                                            break;
                                                        }
                                                    }
                                                });
                                            } else {
                                                var url = link[l].getAttribute('minfin-link').replace(/\/\[([0-9]{4})\]\//, '/' + minfin_data[chart].data[d].data_year + '/');
                                                if (url && url != 'null') {
                                                    link[l].setAttribute('minfin-link', url);
                                                    link[l].setAttribute('tabindex', tabindexcounter);
                                                    tabindexcounter++;
                                                    link[l].addEventListener('click', function (e) {
                                                        var url = this.getAttribute('minfin-link');
                                                        location.href = url + addQuery('graph', '#');
                                                    });
                                                } else { // hide if there's no link
                                                    link[l].style.display = 'none';
                                                }
                                            }
                                        } else {
                                            link[l].style.display = 'none';
                                        }
                                    }
                                }
                            }

                            var nodeTarget = document.getElementById(node.getAttribute('data_id') + "_data");
                            if (nodeTarget) {
                                nodeTarget.setAttribute('id', 'chart' + chart + '_' + nodeTarget.getAttribute('id').replace(/\W/g, '_'));
                            }
                            if (minfin_data[chart].data[d]["children"]) {
                                var nodeTarget = document.getElementById('chart' + chart + '_' + node.getAttribute('data_id').replace(/\W/g, '_') + "_data");
                                if (nodeTarget) {
                                    for (let dd in minfin_data[chart].data[d]["children"]) {
                                        var temp = document.getElementById("chart_legend_data_template" + templateId).innerHTML;
                                        var node = mustache(temp, minfin_data[chart].data[d]["children"][dd], false, chart);
                                        if (minfin_data[chart].meta.childlink && minfin_data[chart].data[d]["children"][dd].identifier) {
                                            if (legendlink.indexOf('internal:') == 0) {
                                                node.classList.add('hover_enabled');
                                                node.setAttribute('minfin-link', minfin_data[chart].data[d]["children"][dd].identifier);
                                                node.setAttribute('tabindex', tabindexcounter);
                                                tabindexcounter++;
                                                node.addEventListener('click', function (e) {
                                                    setDrupalPath('identifier', this.getAttribute('minfin-link'));
                                                    reloadnow(chart);
                                                });
                                            } else if (legendlink.indexOf('csv:') == 0 || legendlink.indexOf('json:') == 0) {
                                                node.classList.add('hover_enabled');
                                                node.setAttribute('minfin-link', legendlink.replace(/\/\[([0-9]{4})\]\//, '/' + minfin_data[chart].data[d].data_year + '/') + '/' + minfin_data[chart].data[d]["children"][dd].identifier);
                                                node.setAttribute('tabindex', tabindexcounter);
                                                tabindexcounter++;
                                                node.addEventListener('click', function (e) {
                                                    //setDrupalPath('identifier', this.getAttribute('minfin-link'));
                                                    var parts = this.getAttribute('minfin-link').split(':');
                                                    parts = parts[1].split('/');
                                                    var fill = 0;
                                                    for (var ds in minfin_api['drupal_structure']) {
                                                        if (!minfin_api['path'][minfin_api['drupal_structure'][ds]] && typeof (parts[fill]) != 'undefined') {
                                                            minfin_api['path'][minfin_api['drupal_structure'][ds]] = parts[fill];
                                                            fill++;
                                                        }
                                                    }
                                                    reloadnow(chart);
                                                });
                                            } else {
                                                node.classList.add('hover_enabled');

                                                var minfinlink = legendlink.replace(/\/\[([0-9]{4})\]\//, '/' + minfin_data[chart].data[d].data_year + '/');
                                                var found = false;
                                                if (typeof (minfin_data[chart].data[d]["children"][dd].link) != 'undefined' && minfin_data[chart].data[d]["children"][dd].link != '') {
                                                    minfinlink = replaceURLPart(minfinlink, minfin_data[chart].data[d]["children"][dd].link);
                                                    found = true;
                                                }
                                                if (!found) {
                                                    minfinlink += minfin_data[chart].data[d]["children"][dd].identifier + '/';
                                                }
                                                node.setAttribute('minfin-link', minfinlink);

                                                node.setAttribute('tabindex', tabindexcounter);
                                                tabindexcounter++;
                                                node.addEventListener('click', function (e) {
                                                    var url = this.getAttribute('minfin-link');
                                                    location.href = url + addQuery('graph', '#');
                                                });
                                            }
                                        } else {
                                            var link = node.querySelectorAll(".chart_legend_data_item_info .minfin_link");
                                            for (let l = 0; l < link.length; l++) {
                                                link[l].addEventListener('click', function (e) {
                                                    var parts = this.getAttribute('minfin-link').replace(/execute:/, '').split('|');
                                                    var func = parts[0];
                                                    parts.shift();
                                                    executeFunctionByName(func, window, parts);
                                                });
                                            }
                                        }

                                        if (minfin_data[chart].options['childinfo']) {
                                            var item = node.querySelector('.chart_legend_data_item');
                                            item.classList.add('link');
                                            item.addEventListener('click', function (e) {
                                                var cntnr = $(this).closest('.chart_legend_data');
                                                var nf = $(this).parent().find('.chart_legend_data_item_info');
                                                var status = $(this).hasClass('active');
                                                cntnr.find('.chart_legend_data_item').each(function () {
                                                    $(this).removeClass('active');
                                                });
                                                cntnr.find('.chart_legend_data_item_info').each(function () {
                                                    $(this).removeClass('show');
                                                });
                                                if (!status) {
                                                    $(this).addClass('active');
                                                    nf.addClass('show');
                                                }
                                                location.href = 'javascript:void(0)';
                                            });
                                        }

                                        nodeTarget.appendChild(node);
                                    }
                                }
                            }
                        }
                        if ($(containernode)[0].scrollHeight > $(containernode).innerHeight()) {
                            $(containernode).parent().find('.chart_legend_data_after').addClass('show');
                        }
                    }
                    if (typeof (minfin_data[chart].meta['legend']) == 'object') {
                        for (var l in minfin_data[chart].meta['legend']) {
                            //TODO other than value / divider -> determined by type for example
                            var divider = typeof (minfin_data[chart].options.divider) != 'undefined' ? minfin_data[chart].options.divider : 1;
                            var data = minfin_data[chart].meta['legend'][l];
                            if (typeof (data['value']) != 'undefined') data['value'] = data['value'] / divider;
                            var legendtitle = typeof (minfin_data[chart].meta['legend'][l]['title']) != 'undefined' ? minfin_data[chart].meta['legend'][l]['title'] : false;
                            var legendtype = typeof (minfin_data[chart].meta['legend'][l]['type']) != 'undefined' ? minfin_data[chart].meta['legend'][l]['type'] : false;
                            if (legendtitle && legendtype) {
                                temp = document.getElementById("chart_legend_template" + templateId).innerHTML;
                                var legendkey = typeof (minfin_data[chart].meta['legend'][l]['key']) != 'undefined' ? minfin_data[chart].meta['legend'][l]['key'] : false;
                                if (legendkey) {
                                    var sum = typeof (minfin_data[chart].meta['legend'][l]['sum']) != 'undefined' ? minfin_data[chart].meta['legend'][l]['sum'] : 'average';
                                    var calcval = 0;
                                    var countval = 0;
                                    for (var md in minfin_data[chart].data) {
                                        if (minfin_data[chart].data[md][legendkey]) {
                                            calcval += minfin_data[chart].data[md][legendkey];
                                            countval++;
                                        }
                                    }
                                    if (sum == 'average') {
                                        calcval /= countval;
                                    }
                                    data['value'] = calcval;
                                }

                                node = mustache(temp, data, -1, chart);
                                node.setAttribute('data_id', node.getAttribute('id'));
                                node.setAttribute('tabindex', tabindexcounter);
                                tabindexcounter++;
                                node.setAttribute('id', 'chart' + chart + '_' + node.getAttribute('id').replace(/\W/g, '_'));
                                node.classList.add('manual_legend');
                                if (i != 0 && l == 0) {
                                    node.classList.add('first_manual_legend');
                                }
                                target.appendChild(node);

                                var color = am4core.color(get_minfin_color(minfin_data[chart].meta['legend'][l]['color']));
                                var colorObj = document.querySelector("#" + node.getAttribute('id') + " .chart_legend_item_color");
                                var legendcolor = get_minfin_color(minfin_data[chart].meta['legend'][l]['color']);
                                var color = am4core.color(legendcolor)._value;
                                if (legendtype == 'fill') {
                                    colorObj.style.backgroundColor = 'rgb(' + color.r + ',' + color.g + ',' + color.b + ')';
                                    colorObj.setAttribute('maincol', 'rgb(' + color.r + ',' + color.g + ',' + color.b + ')');
                                } else if (legendtype == 'pattern') {
                                    colorObj.style.backgroundColor = 'rgb(' + color.r + ',' + color.g + ',' + color.b + ')';
                                    colorObj.setAttribute('maincol', 'rgb(' + color.r + ',' + color.g + ',' + color.b + ')');
                                    var col = color.r + '' + color.g + '' + color.b;
                                    colorObj.style.backgroundImage = "url('data:image/svg+xml;charset=utf-8;base64," + minfin_patterns[col] + "')";
                                    colorObj.style.backgroundSize = '15px 15px';
                                    colorObj.style.backgroundColor = '';
                                } else if (legendtype == 'line') {
                                    colorObj.style.borderColor = 'rgb(' + color.r + ',' + color.g + ',' + color.b + ')';
                                    colorObj.setAttribute('maincol', 'rgb(' + color.r + ',' + color.g + ',' + color.b + ')');
                                    colorObj.classList.add("line");
                                } else if (legendtype == 'bullet') {
                                    colorObj.style.borderColor = 'rgb(' + color.r + ',' + color.g + ',' + color.b + ')';
                                    colorObj.setAttribute('maincol', 'rgb(' + color.r + ',' + color.g + ',' + color.b + ')');
                                    colorObj.classList.add("bullet");
                                    colorObj.classList.add("bullet_" + i);
                                    var styleElem = document.head.appendChild(document.createElement("style"));
                                    styleElem.innerHTML = '.bullet_' + i + ':before {background-color: rgb(' + color.r + ',' + color.g + ',' + color.b + ');}';
                                } else if (legendtype == 'dash') {
                                    colorObj.style.borderColor = 'rgb(' + color.r + ',' + color.g + ',' + color.b + ')';
                                    colorObj.setAttribute('maincol', 'rgb(' + color.r + ',' + color.g + ',' + color.b + ')');
                                    colorObj.classList.add("dash");
                                } else if (legendtype == 'other') {
                                    node.classList.add('navigation');
                                    node.classList.add('center');
                                    node.addEventListener('click', (function (e) {
                                        if (typeof (e['link']) != 'undefind' && e['link'].indexOf('execute:') == 0) {
                                            var parts = e['link'].replace(/execute:/, '').split('|');
                                            var func = parts[0];
                                            parts.shift();
                                            parts.push(chart);
                                            console.log(minfin_data[chart].meta, minfin_data[chart].options, parts)
                                            executeFunctionByName(func, window, parts);
                                        }
                                    }).bind(null, data));
                                }
                            }
                        }
                    }
                }

                var carousel = document.querySelector('#chart_navigation #chart_nav_carousel');
                //if ($(carousel).hasClass('bar')) {
                $('[curyear="no"]').css('display', 'none');
                //}

                /*
                resize
                */
                //resize_graph(2000);
            };

            /*
            // Replace part of url
            */
            function replaceURLPart(url, part) {
                for (var u in url) {
                    if (part.indexOf(url.substring(u)) == 0) {
                        url = url.substring(0, u) + part;
                        break;
                    }
                }
                return url;
            }

            /*
            // Get and fill template
            */
            function mustache(temp, replace, index, chart, short) {

                var minus = (replace.extra && replace.extra.indexOf('minus') > -1) ? true : false;
                if (typeof (chart) == 'undefined' || index === false) {
                    index = -1;
                }
                if (typeof (short) == 'undefined') {
                    short = false;
                }
                var matches = temp.match(/{+(.*?)}+/g);
                for (let m in matches) {
                    var force = false;
                    var re = new RegExp(matches[m], "g");
                    if (matches[m].indexOf('num_') > -1) { // needs documentation
                        matches[m] = matches[m].replace('num_', '');
                        force = 'value';
                    } else if (matches[m].indexOf('eur_') > -1) {
                        matches[m] = matches[m].replace('eur_', '');
                        opt['currency'] = 'EUR';
                        opt['round'] = 'round';
                        force = 'value';
                    }
                    var key = matches[m].replace(/{{/, '').replace(/}}/, '');
                    if (key == 'description' && typeof (replace['data_year']) != 'undefined' && typeof ('description_' + replace['data_year']) != 'undefined') {
                        key = 'description_' + replace['data_year'];
                    }
                    var str = typeof (replace[key]) != 'undefined' ? replace[key] : '';
                    var opt = [];
                    var templateId = '';
                    if (typeof (chart) != 'undefined' && minfin_data[chart].options && minfin_data[chart].options['tamplate_id'] && minfin_data[chart].options['tamplate_id'] != '') {
                        templateId = '_' + minfin_data[chart].options['tamplate_id'];
                    }
                    //script_template_id: 'mek'// needs documentation
                    if (!force && typeof (chart) != 'undefined' && minfin_data[chart].meta) {
                        for (var md in minfin_data[chart].meta) {
                            opt[md] = minfin_data[chart].meta[md];
                        }
                        if (replace['currency']) {
                            opt['currency'] = replace['currency'];
                        }
                        if (replace['round']) {
                            opt['round'] = replace['round'];
                        }
                        if (replace['divider']) {
                            opt['divider'] = replace['divider'];
                        }
                        if (replace['decimals']) {
                            opt['decimals'] = replace['decimals'];
                        }
                    }
                    if (key == 'value' || force == 'value') {

                        if (minfin_data[chart].options['nonulvalues'] && str == 0) {
                            str = "";
                        } else {

                            if (isNaN(str) && !isNaN(replace[str])) {
                                str = format_number(round_number(replace[str], opt), opt);
                            } else {
                                str = format_number(round_number(str, opt), opt);
                            }

                            if (replace.range) {
                                str = replace.range;
                            } else if (minus) {
                                str = '- ' + str;
                            }
                        }
                    }
                    if (key == 'amount') {
                        str = format_number(divide_number(str, opt), opt);
                        if (replace.range) {
                            str = replace.range;
                        }
                        if (minus) {
                            str = '- ' + str;
                        }
                    }
                    if (key == 'legend_total') {
                    }
                    // shorten info
                    if (short && str) {
                        if (typeof (str) != 'string') {
                            str = '' + str;
                        }
                        var len = str.split(' ');
                        if (len.length > short) {
                            str = "Zie legenda voor volledige tekst.";
                        }
                    }
                    temp = temp.replace(re, str);
                }
                var listHtml = new DOMParser().parseFromString(temp, 'text/html');
                var node = listHtml.getElementsByTagName('body')[0].children[0];
                if (index > -1) {
                    node.setAttribute('index', index);
                }
                return node;
            }

            /*
            // keep track of the mouse
            */
            document.addEventListener('mousemove', function (e) {
                if (!curKeyDown) {
                    mousePos.x = e.clientX;
                    mousePos.y = e.clientY;
                    var target = document.getElementById("chart_popup");
                    if (target) {
                        var open = target ? parseInt(target.getAttribute('open')) : '-';
                        if (isNaN(open)) {
                            setPopupTop();
                        }
                    }
                }
            });

            /*
            // Set slice status default/inactive
            */
            function setSlice(chart, index, closePopup, alwaysOn, group, fromlegend) {

                // reset all slices from former active chart
                var target = document.getElementById("chart_popup");
                var popen = target ? parseInt(target.getAttribute('open')) : '-';
                var pchart = target ? parseInt(target.getAttribute('chart')) : -1;
                if (closePopup != false && !isNaN(pchart) && !isNaN(popen) && pchart != chart && pchart != -1) {
                    setTimeout(function (pchart, popen) {
                        setSlice(pchart, popen, false);
                    }, 100, pchart, popen);
                }

                var reset = false;
                var all = true;
                minfin_api.select = '';
                if (typeof (alwaysOn) == 'undefined') {
                    alwaysOn = false;
                }
                if (typeof (group) == 'undefined') {
                    group = false;
                }
                // first test if reset should be set
                var local = true;
                for (let s in series) {
                    if (series[s].chartId == chart) {
                        local = charts[series[s].chartId].local;
                        if (typeof (closePopup) == 'undefined') {
                            closePopup = true;
                        }
                        var slice = series[s].slices ? series[s].slices : series[s].columns ? series[s].columns : series[s].links ? series[s].links : false;
                        if (slice) {
                            var numberofslices = [];
                            slice.each(function (item) {
                                var testId = item._dataItem.component.legendId ? parseInt(item._dataItem.component.legendId) : item._dataItem._index;
                                if (numberofslices.indexOf(testId) == -1) numberofslices.push(testId);
                                if (index == testId && (item.state == "default" || item.state == "active")) {
                                    reset = true;
                                    //if (slice._values.length == 1) all = false;
                                }
                                else if (index != testId && item.state == "inactive") {
                                    all = false;
                                }
                            });
                            if (numberofslices.length == 1) all = false;
                        }
                    }
                }
                if (all || alwaysOn) {
                    reset = false;
                }
                // set or reset
                var columnYPostions = [];
                var lowest = 0;
                for (let s in series) {
                    series[s]['reset'] = reset;
                    if (!columnYPostions[series[s].chartId]) columnYPostions[series[s].chartId] = [];
                    if (!local || series[s].chartId == chart) {
                        if (typeof (closePopup) == 'undefined') {
                            closePopup = true;
                        }
                        var all = true;
                        var slice = series[s].slices ? series[s].slices : series[s].columns ? series[s].columns : series[s].links ? series[s].links : false;
                        if (slice) {
                            slice.each(function (item) {
                                item._dataItem.component.reset = reset;
                                var testId = item._dataItem.component.legendId ? parseInt(item._dataItem.component.legendId) : item._dataItem._index;
                                var testGroup = item._dataItem.component.uniqueGroup ? item._dataItem.component.uniqueGroup : 'nogroup';
                                //console.log('test: ', group, testGroup)
                                if (!reset && index != testId && group != testGroup) {
                                    item.toBack();
                                    item.setState("inactive");
                                    item.state = "inactive";
                                    if (item._dataItem._className == 'ColumnSeriesDataItem' && lowest < item.realY + item._dataItem.itemHeight) {
                                        lowest = item.realY + item._dataItem.itemHeight;
                                    }
                                    if (item._dataItem._className == 'SankeyDiagramDataItem') {
                                        item.middleLine.setState("inactive");
                                    }
                                } else {
                                    if (item._dataItem._className == 'SankeyDiagramDataItem') {
                                        item.middleLine.setState("default");
                                    }
                                    item.setState("default");
                                    item.state = "default";
                                    if (reset) item.state = "undefined";
                                    if (!reset) {
                                        switch (item._dataItem._className) {
                                            case 'SankeyDiagramDataItem':
                                                item.setState("active");
                                                item.state = "active";
                                                minfin_api.select = minfin_data[chart].data[index].title;
                                                break;
                                            case 'ColumnSeriesDataItem':
                                                // store selection info for bartotals
                                                var l = item._dataItem.values.valueY.value >= 0 ? item.realY : item.realY - item._dataItem.itemHeight;
                                                columnYPostions[series[s].chartId].push({
                                                    category: item._dataItem.categories.categoryX,
                                                    position: l,
                                                    value: item._dataItem.values.valueY.value,
                                                    minus: typeof (series[s].extra) != 'undefined' && series[s].extra.indexOf('minus') > -1 ? true : false,
                                                    lowest: item.realY + item._dataItem.itemHeight + 20
                                                });
                                                if (lowest < item.realY + item._dataItem.itemHeight) {
                                                    lowest = item.realY + item._dataItem.itemHeight;
                                                }
                                                minfin_api.select = minfin_data[chart].data[index].identifier;
                                                break;
                                            case 'PieSeriesDataItem':
                                                minfin_api.select = minfin_data[chart].data[index].identifier;
                                                break;
                                        }
                                    }
                                }
                            });
                        }
                    }
                }

                // set segment or bartotal above column
                for (let c in charts) {
                    if (charts[c].bartotals) {

                        // get sum of bar totals
                        var columnYPostions2 = {};
                        var lowest = 0;
                        for (let cp in columnYPostions[c]) {
                            var cbar = columnYPostions[c][cp]['category'];
                            if (typeof (columnYPostions2[cbar]) == 'undefined') {
                                columnYPostions2[cbar] = {
                                    position: 10000,
                                    value: 0
                                }
                            }
                            if (columnYPostions[c][cp]['position'] < columnYPostions2[cbar]['position'] && columnYPostions[c][cp]['value']) {
                                columnYPostions2[cbar]['position'] = columnYPostions[c][cp]['position'];
                            }
                            columnYPostions2[cbar]['value'] += columnYPostions[c][cp]['value'];
                            if (lowest < columnYPostions[c][cp]['lowest']) {
                                //lowest = columnYPostions[chart][cp]['lowest'];
                            }
                        }

                        charts[c].barValues = false;
                        if (columnYPostions[c].length) {
                            charts[c].barValues = columnYPostions[c];
                            for (let l in labels[c].bar_totals) {
                                var label = labels[c].bar_totals[l];
                                var val = typeof (columnYPostions2[columnYPostions[c][l].category]) != 'undefined' ? columnYPostions2[columnYPostions[c][l].category].value : 0;
                                if (columnYPostions[c][l] && columnYPostions[c][l].minus) {
                                    val *= -1;
                                }
                                var pos = val == 0 ? lowest : val < 0 ? columnYPostions2[columnYPostions[c][l].category].position : columnYPostions2[columnYPostions[c][l].category].position;
                                var fix = typeof (minfin_data[c].options['highlightbarfix']) == 'undefined' || !minfin_data[c].options['highlightbarfix'] ? true : false;
                                if ((fix || minfin_data[c].options['singlebars']) && typeof (columnYPostions[c][0]['category']) != 'undefined') {
                                    var posx = minfin_data[c].options.bars.indexOf(columnYPostions[c][0].category);
                                    label.x = am4core.percent((parseInt(posx) * (100 / labels[c].bar_totals.length)) + (0.5 * (100 / labels[c].bar_totals.length)));
                                }
                                if (!pos) {
                                    pos = 10000;
                                }
                                label.text = format_number(val, minfin_data[c].meta);
                                label.y = pos - 2;
                            }
                        } else if (c == chart) {
                            barTotals(c);
                        }
                    }
                    //console.log(c, labels[c])
                }
                // toggle corresponding legend
                toggleLegend(chart, index, reset ? 0 : 1, local, fromlegend);

                return reset;
            }

            /*
            // Toggle legend item
            */
            function toggleLegend(chart, index, open, local, fromlegend) {
                for (let c in charts) {
                    if (!local || c == chart) {
                        var legendId = document.querySelector('[chartid="' + c + '"]:not(.chart_navigation):not(.chart_navigation_top)').id + '_legend';
                        var count = document.querySelectorAll('[id="' + legendId + '"] [index]').length;
                        for (let n = 0; n < count; n++) {
                            var legendObj = legendId ? document.querySelector('[id="' + legendId + '"] [index="' + n + '"]') : false;
                            if (legendObj) {
                                var data_id = legendObj.getAttribute('data_id');
                                var maintarget = document.getElementById('chart' + c + '_' + data_id.replace(/\W/g, '_'));
                                var target = document.querySelectorAll('#chart' + c + '_' + data_id.replace(/\W/g, '_') + ' .chart_legend_sub_data');
                                var subtarget = document.querySelectorAll('#chart' + c + '_' + data_id.replace(/\W/g, '_') + ' .chart_legend_pre_data');
                                var maintargetheader = document.querySelector('[id="chart' + c + '_' + data_id.replace(/\W/g, '_') + '"] .chart_legend_item_header');
                                var maintargetcolor = document.querySelector('[id="chart' + c + '_' + data_id.replace(/\W/g, '_') + '"] .chart_legend_item_color').getAttribute('maincol');
                                var col = maintargetcolor.split('(');
                                col = col[1].split(')');
                                col = col[0].split(',');
                                col = [parseInt(col[0]), parseInt(col[1]), parseInt(col[2])];
                                var colLight = 'rgb(' + parseInt(col[0] + (255 - col[0]) / 2) + ',' + parseInt(col[1] + (255 - col[1]) / 2) + ',' + parseInt(col[2] + (255 - col[2]) / 2) + ')';
                                if (target[0] && subtarget[0]) {
                                    subtarget[0].style.backgroundColor = colLight;
                                }
                                col = col[0] + col[1] + col[2];
                                col = col > 384 ? 'black' : 'white';
                                if (n != index || !open) {
                                    if (maintarget) {
                                        maintarget.classList.remove('selected');
                                        maintargetheader.style.backgroundColor = '';
                                        maintargetheader.style.color = '';
                                    }
                                    if (target[0]) {
                                        target[0].style.maxHeight = 0;
                                        target[0].classList.remove('open');
                                        setTimeout(function (layer) {
                                            layer.style.maxHeight = 0;
                                        }, 300, target[0]);
                                    }
                                }
                                else {
                                    if (maintarget) {
                                        maintarget.classList.add('selected');
                                        maintargetheader.style.backgroundColor = maintargetcolor;
                                        maintargetheader.style.color = col;
                                    }
                                    if (target[0]) {
                                        //target[0].style.display = 'block';
                                        target[0].classList.add('open');
                                        target[0].style.maxHeight = '1500px';
                                        setTimeout(function (layer) {
                                            layer.style.maxHeight = 'none'; //(layer.offsetHeight) + 'px';
                                        }, 300, target[0]);
                                    }
                                    // scroll into view
                                    if (!fromlegend) {
                                        setTimeout(function (maintarget) {
                                            $(maintarget).parent().scrollTop($(maintarget).position().top);
                                        }, 100, maintarget);
                                    }
                                }
                            }
                        }
                    }
                }
            }

            /*
            // Show popup on hover
            */
            function show_popup(chart, index, openup, item) {
                // load correct value into mustache

                var templateId = '';
                if (minfin_data[chart].options && minfin_data[chart].options['script_template_id'] && minfin_data[chart].options['script_template_id'] != '') {
                    templateId = '_' + minfin_data[chart].options['script_template_id'];
                }

                var year = 0;
                if (item && item._dataItem._className == 'ColumnSeriesDataItem') {
                    minfin_data[chart].data[index].value = minfin_data[chart].data[index][item._dataItem.categories.categoryX];
                    year = minfin_data[chart].data[index][item._dataItem.categories.categoryX] ? item._dataItem.categories.categoryX : item._dataItem.component.barId;
                    minfin_data[chart].data[index].data_year = year; //'YO';
                    getPerc(chart, index);
                }
                else if (item && item._dataItem._className == 'PieSeriesDataItem') {
                    minfin_data[chart].data[index].value = minfin_data[chart].data[index].defaultValue;
                    year = minfin_data[chart].data[index].data_year;
                    getPerc(chart, index);
                }
                else if (item && item._dataItem._className == 'SankeyDiagramDataItem') {
                    // no perc
                }
                if (typeof (openup) == 'undefined') {
                    openup = false;
                }
                if (closeTimer) {
                    clearTimeout(closeTimer);
                }
                if (openTimer) {
                    clearTimeout(openTimer);
                }
                openTimer = setTimeout(function (index, openup, chart, year) {
                    sluit_popup(false, false);
                    var target = document.getElementById("chart_popup");
                    var open = target ? parseInt(target.getAttribute('open')) : '-';
                    if (!target) {
                        var target = document.getElementsByTagName("body")[0];
                        var temp = document.getElementById("chart_popup_template" + templateId).innerHTML;
                        // short info in popup
                        var node = mustache(temp, minfin_data[chart].data[index], false, chart, 40);

                        // add manualinfo about patterns
                        if (minfin_data[chart].data[index].extra && minfin_data[chart].data[index].extra.indexOf('pattern') > -1 && typeof (minfin_data[chart].data[index]['group']) != 'undefined') {
                            var txt = node.querySelectorAll(".description");
                            if (txt.length) {
                                txt = txt[0].innerHTML;
                                if (txt != '') {
                                    txt += '<br/><br/>';
                                } else {
                                    txt += '<br/>';
                                }
                                txt += patternTxt;
                                node.querySelectorAll(".description")[0].innerHTML = txt;
                            }
                        }

                        var link = node.getElementsByClassName('minfin_link');
                        if (link.length) {
                            for (let l = 0; l < link.length; l++) {
                                if (link[l].getAttribute('minfin-link') && link[l].getAttribute('minfin-link') != '' && link[l].getAttribute('minfin-link') != 'null') {
                                    if (link[l].getAttribute('minfin-link').indexOf('ext:') == 0) {
                                        var url = link[l].getAttribute('minfin-link').replace(/ext:/, '');
                                        link[l].setAttribute('minfin-link', url);
                                        link[l].addEventListener('click', function (e) {
                                            var year = e.target.getAttribute('minfin-year');
                                            var origin = window.location.origin;
                                            var link = e.target.getAttribute('minfin-link').replace(/\/\[([0-9]{4})\]\//, '/' + year + '/');
                                            if (minfin_api && minfin_api.data && minfin_api.data.year) {
                                                link = link.replace('/visuals/' + minfin_api.data.year + '/', '/visuals/' + year + '/');
                                            }
                                            link = link.indexOf('/') == 0 ? origin + link : link;
                                            location.href = link + addQuery(['graph', '#']);
                                        });
                                    } else if (link[l].getAttribute('minfin-link').indexOf('json:') == 0 || link[l].getAttribute('minfin-link').indexOf('csv:') == 0) {
                                        var url = link[l].getAttribute('minfin-link').replace(/\/\[([0-9]{4})\]\//, '/' + minfin_data[chart].data[index].data_year + '/');
                                        link[l].setAttribute('minfin-link', url);
                                        link[l].addEventListener('click', function (e) {
                                            for (var ds in minfin_api['drupal_structure']) {
                                                if (!minfin_api['path'][minfin_api['drupal_structure'][ds]]) {
                                                    minfin_api['path'][minfin_api['drupal_structure'][ds]] = minfin_api['select'];
                                                    reloadnow(chart);
                                                    break;
                                                }
                                            }
                                        });
                                    } else if (link[l].getAttribute('minfin-link').indexOf('internal:') == 0) {
                                        if ((typeof (minfin_data[chart].data[index]['self_info']) != 'undefined' && minfin_data[chart].data[index]['self_info']) || (typeof (minfin_data[chart].data[index]['child_info']) != 'undefined' && minfin_data[chart].data[index]['child_info'])) {
                                            link[l].addEventListener('click', function (e) {
                                                var parts = this.getAttribute('minfin-link').split('/');
                                                setDrupalPath('identifier', parts[parts.length - 1]);
                                                reloadnow(chart);
                                            });
                                        } else {
                                            link[l].style.display = 'none';
                                        }
                                    } else if (link[l].getAttribute('minfin-link').indexOf('execute:') == 0) {
                                        link[l].addEventListener('click', function (e) {
                                            var parts = this.getAttribute('minfin-link').replace(/execute:/, '').split('|');
                                            var func = parts[0];
                                            parts.shift();
                                            parts.push(chart);
                                            executeFunctionByName(func, window, parts);
                                        });
                                    } else {
                                        link[l].setAttribute('minfin-year', minfin_data[chart].data[index].data_year);
                                        link[l].addEventListener('click', function (e) {
                                            var year = e.target.getAttribute('minfin-year');
                                            var link = e.target.getAttribute('minfin-link').replace(/\/\[([0-9]{4})\]\//, '/' + year + '/');
                                            if (minfin_api && minfin_api.data && minfin_api.data.year) {
                                                link = link.replace('/visuals/' + minfin_api.data.year + '/', '/visuals/' + year + '/');
                                            }
                                            location.href = link + addQuery(['graph', '#']);
                                        });
                                    }
                                } else {
                                    link[l].style.display = 'none';
                                }
                            }
                        }
                        node.addEventListener('mouseover', function (e) {
                            if (closeTimer) {
                                clearTimeout(closeTimer);
                            }
                        });
                        node.addEventListener('mouseout', function (e) {
                            sluit_popup();
                        });

                        target.appendChild(node);
                        //console.log(node);

                        var nodeTarget = document.getElementById("chart_popup_data");
                        if (1 == 2 && minfin_data[chart].data[index]["data"]) { // geen children in de popup
                            for (let dd in minfin_data[chart].data[index]["data"]) {
                                var temp = document.getElementById("chart_legend_data_template" + templateId).innerHTML;
                                var node = mustache(temp, minfin_data[chart].data[index]["data"][dd], false, chart);
                                if (nodeTarget) {
                                    nodeTarget.appendChild(node);
                                }
                            }
                        }
                        else {
                            nodeTarget.parentNode.removeChild(nodeTarget);
                        }

                        // Keep popup open
                        if (openup) {
                            var closeObj = document.querySelector("#chart_popup .close_popup");
                            closeObj.addEventListener('click', function (e) {
                                sluit_popup(-1, false);
                            });
                            var target = document.getElementById("chart_popup");
                            if (target) {
                                target.setAttribute('open', index);
                                target.setAttribute('chart', chart);
                                target = document.getElementById("chart_popup_info");
                                if (target) {
                                    target.style.display = 'block';
                                }
                            }
                            var target = document.getElementsByClassName("close_popup");
                            target[0].style.display = 'block';
                            var target = document.getElementsByClassName("arrow_popup");
                            target[0].style.display = 'block';
                        }

                        if (minfin_data[chart].data[index]['no_card']) {
                            if (node.querySelector('.chart_legend_card')) {
                                node.querySelector('.chart_legend_card').style.display = 'none';
                            }
                            if (node.querySelector('.chart_popup_info')) {
                                node.querySelector('.chart_popup_info').style.display = 'none';
                            }
                            if (node.querySelector('.chart_popup_no_card')) {
                                node.querySelector('.chart_popup_no_card').style.display = 'block';
                            }
                        }

                        if (item) {
                            mousePos.item = item;
                        }
                        setPopupTop(chart);
                    }
                    else {

                    }
                }, openup ? 0 : _minfin_charts_options.delay, index, openup, chart, year);
            }

            /*
            // Open popup on click
            */
            function open_popup(chart, index, item) {

                var target = document.getElementById("chart_popup");
                var open = target ? parseInt(target.getAttribute('open')) : '-';
                mousePos.py = target ? parseInt(target.style.top) : false;

                sluit_popup(true, false);
                if (open != index) {
                    show_popup(chart, index, true);
                }
            }

            /*
            // Close popup
            */
            function sluit_popup(always, delay) {
                if (typeof (always) == 'undefined') {
                    always = false;
                }
                if (typeof (delay) == 'undefined') {
                    delay = true;
                }
                if (openTimer) {
                    clearTimeout(openTimer);
                }
                var target = document.getElementById("chart_popup");
                var open = target ? parseInt(target.getAttribute('open')) : '-';
                var chart = target ? parseInt(target.getAttribute('chart')) : 0;
                if (target && (isNaN(open) || always)) {
                    if (closeTimer) {
                        clearTimeout(closeTimer);
                    }
                    if (delay) {
                        closeTimer = setTimeout(function (target, open, always) {
                            if (always == -1) {
                                setSlice(0, open, false);
                            }
                            target.parentNode.removeChild(target);
                        }, _minfin_charts_options.delay, target, open, always);
                    }
                    else {
                        if (always == -1) {
                            setSlice(chart, open, false);
                        }
                        target.parentNode.removeChild(target);
                    }
                }
            }

            /*
            // Calculate popup position
            */
            function setPopupTop(domid) {

                var target = document.getElementById("chart_popup");
                if (isNaN(domid)) {
                    domid = parseInt(target.getAttribute('chartId'));
                }
                target.setAttribute('chartId', domid);

                var style = target ? target.currentStyle || window.getComputedStyle(target) : false;
                var paddding = style ? parseInt(style.paddingLeft) + parseInt(style.paddingRight) : 0;
                var open = target ? parseInt(target.getAttribute('open')) : '-';

                var item = mousePos.item;
                var doc = document.documentElement;
                var chart = document.getElementById(charts[domid].domId);
                var bbox = chart.getBoundingClientRect();
                var width = bbox.width;
                if (width > 351) {
                    width = 351;
                }
                var left = (window.pageXOffset || doc.scrollLeft) - (doc.clientLeft || 0);
                var cursorleft = left;
                left = bbox.left + (bbox.width - paddding) / 2 + left - width / 2;
                if (left < 10) {
                    left = 10;
                }
                var topAnim = 0;
                var top = 32 + mousePos.y + (window.pageYOffset || doc.scrollTop) - (doc.clientTop || 0);

                if (item && item._className == 'Column') {
                    left = bbox.left + (((mousePos.x - bbox.left) / bbox.width) * (bbox.width - width));
                    if (left < 10) {
                        left = 10;
                    }
                }

                if (!isNaN(open) && item) {
                    var target = document.getElementById(charts[domid].domId);
                    var box = target.getBoundingClientRect();
                    if (item._className == 'SankeyLink') {
                        var topAnim = $('#' + charts[domid].domId).offset().top + 68 + item._bbox.y + (item._bbox.height / 2);
                        var square = item._bbox;
                        var dotY = square.y + square.height * 0.85;
                        //var topAnim = dotY + 16 + (window.pageYOffset || doc.scrollTop) - (doc.clientTop || 0);
                        //
                        var dotX = square.x + square.width * 0.5;
                        //console.log('square', square, dotY, topAnim, dotX, top)
                    }
                    else if (item._className == 'Column') {
                        var topAnim = mousePos.py + 12 + (window.pageYOffset || doc.scrollTop) - (doc.clientTop || 0);
                        var square = item.group.node.getBoundingClientRect();
                        var dotY = square.top + square.height * 0.85;
                        var topAnim = dotY + 16 + (window.pageYOffset || doc.scrollTop) - (doc.clientTop || 0);
                        //top = topAnim;
                        var dotX = square.left + square.width * 0.5;
                        top = mousePos.py ? mousePos.py : topAnim;
                    }
                    else {
                        var radius = item;
                        var startAngle = item.properties.startAngle + 90;
                        var arc = item.properties.arc;
                        var endAngle = Math.round(startAngle + arc);
                        var minArc = arc < 8 ? arc / 2 : 4;

                        var angle = startAngle + arc / 2;
                        if (startAngle < 180 && endAngle < 180) {
                            angle = endAngle - minArc;
                        }
                        if (startAngle > 180 && endAngle < 360) {
                            angle = startAngle + minArc;
                        }
                        if (startAngle < 180 && endAngle > 180) {
                            angle = 180;
                        }
                        if (startAngle < 360 && endAngle > 360) {
                            angle = 0;
                        }

                        while (angle >= 360) {
                            angle -= 360;
                        }
                        while (angle < 0) {
                            angle += 360;
                        }

                        var innerRadius = item.properties.innerRadius;
                        var radius = item.properties.radius;
                        var factor = (1 - (Math.abs(angle - 180) / 180))

                        radius = innerRadius + (radius - innerRadius) * factor;
                        var midX = box.left + box.width / 2;
                        var midY = (box.top + box.height / 2); // + ((radius - innerRadius)
                        // / 2);
                        var dotX = midX + Math.sin((Math.PI / 180) * angle) * radius;
                        var dotY = midY - Math.cos((Math.PI / 180) * angle) * radius;
                        var topAnim = dotY + 12 + (window.pageYOffset || doc.scrollTop) - (doc.clientTop || 0);
                        top = mousePos.py ? mousePos.py : topAnim;

                    }
                }

                var target = document.getElementsByClassName("arrow_popup");
                var arrow_pos = (dotX - left) < 21 ? 21 : (dotX - left) < 330 ? (dotX - left) : 330;
                target[0].style.left = arrow_pos + 'px';

                var target = document.getElementById("chart_popup");
                target.style.top = (top) + 'px';
                if (mousePos.py !== false && top != topAnim) {
                    movePopup(target, top, topAnim, top > topAnim ? -10 : 10);
                    target.style.top = (topAnim) + 'px';
                    mousePos.py = false;
                }
                target.style.left = left + 'px';
                target.style.width = width + 'px';
            }

            /*
            // move popup in place
            */
            function movePopup(target, current, goal, step) {
                current += step;
                if (step >= 0 && current > goal) {
                    current = goal;
                }
                if (step <= 0 && current < goal) {
                    current = goal;
                }
                target.style.top = current + 'px';
                if (current != goal) {
                    setTimeout(function (target, current, goal, step) {
                        movePopup(target, current, goal, step);
                    }, 0, target, current, goal, step)
                }
            }

            /*
            // Get url query vars
            */
            var queryParams;
            (window.onpopstate = function () {
                var match;
                var pl = /\+/g;
                var search = /([^&=]+)=?([^&]*)/g;
                var decode = function (s) {
                    return decodeURIComponent(s.replace(pl, " "));
                };
                var query = window.location.search.substring(1);
                queryParams = {};
                while (match = search.exec(query)) {
                    queryParams[decode(match[1])] = decode(match[2]);
                }
            })();

            /*
            // Get url path vars
            */
            function getDrupalParts() {
                var vars = {};
                minfin_api.parts = window.location.href.split('/');

                // change api_root except for exceptions
                var exception = -1;
                for (var exc in minfin_api.api_exceptions) {
                    if (minfin_api.api_exceptions[exc], window.location.origin, window.location.origin.indexOf(minfin_api.api_exceptions[exc]) > -1) {
                        exception = exc;
                    }
                }
                if (exception == -1) {
                    //console.warn('API root changed to ' + window.location.origin);
                    minfin_api.api_root = window.location.origin;
                } else {
                    //console.warn('API root ' + minfin_api.api_root + ' unchanged from ' + window.location.origin);
                }

                // seek trigger
                for (let p in minfin_api.parts) {
                    if (minfin_api.parts[p] == minfin_api.drupal_structure[0]) {
                        minfin_api.index = parseInt(p);
                        break;
                    }
                }
                // set parts from trigger
                if (minfin_api.index) {
                    var lastIndex = 0;
                    for (let p = minfin_api.index; p < minfin_api.parts.length; p++) {
                        // use querystring
                        if (minfin_api.parts[p].indexOf('&') == 0 || minfin_api.parts[p].indexOf('&graph') >= 0 || minfin_api.parts[p].indexOf('?') >= 0) {
                            lastIndex = 1;
                        }
                        if (minfin_api.parts[p].indexOf('?') > 0) {
                            var spltqm = minfin_api.parts[p].split('?');
                            minfin_api.parts[p] = spltqm[0];
                            vars[minfin_api.drupal_structure[p - minfin_api.index]] = decodeURIComponent(minfin_api.parts[p]);
                        }
                        if (minfin_api.parts[p].indexOf('&graph') > 0) {
                            var spltqm = minfin_api.parts[p].split('&');
                            minfin_api.parts[p] = spltqm[0];
                            vars[minfin_api.drupal_structure[p - minfin_api.index]] = decodeURIComponent(minfin_api.parts[p]);
                        }
                        if (!lastIndex && p && minfin_api.parts[p] != '') {
                            vars[minfin_api.drupal_structure[p - minfin_api.index]] = decodeURIComponent(minfin_api.parts[p]);
                        }
                    }
                }
                for (let v in vars) {
                    minfin_api.data[v] = vars[v]; // definite
                    minfin_api.path[v] = vars[v]; // may change for next link
                }
                // remove last empty part
                if (minfin_api.parts[minfin_api.parts.length - 1] == '') minfin_api.parts.pop();
                // add uitgaven as default if not defined
                // if(minfin_api.data['year'] && minfin_api.data['phase'] && !minfin_api.data['vuo']) {
                // 		minfin_api.data['vuo'] = 'U';
                // 		minfin_api.path['vuo'] = 'U';
                // 		minfin_api.parts.push('U');
                // 	}
                // retrieve focus from querystring
                minfin_api.data['category'] = queryParams.category ? queryParams.category : queryParams.cat ? queryParams.cat : false;
            }

            /*
            // Set individual parts for Drupal path
            // For use with dropdown selection
            */
            function setDrupalPath(key, value) {
                if (minfin_api.index) {
                    if (typeof (key) == 'string' && typeof (value) != 'undefined') {
                        for (let p = minfin_api.index; p < minfin_api.parts.length; p++) {
                            if (found) {
                                minfin_api.path[minfin_api.drupal_structure[p - minfin_api.index]] = false;
                            }
                            if (minfin_api.drupal_structure[p - minfin_api.index] == key) {
                                minfin_api.path[minfin_api.drupal_structure[p - minfin_api.index]] = '' + value;
                            }
                        }
                    }
                } else if (key == 'chartId' && minfin_api.select) {
                    var index = 0;
                    for (var ds in minfin_api['drupal_structure']) {
                        if (minfin_api['path'][minfin_api['drupal_structure'][ds]]) {
                            index = ds;
                        }
                    }
                    if (index) {
                        delete minfin_api['path'][minfin_api['drupal_structure'][index]];
                        if (value) {
                            reloadnow(value);
                        } else {
                            console.warn('chartId missing in chart_navigation');
                        }
                    }
                } else {
                    minfin_api.path[key] = '' + value;
                    var found = false;
                    var ta = {};
                    for (let p in minfin_api.path) {
                        if (!found) {
                            ta[p] = minfin_api.path[p];
                        }
                        if (p == key) {
                            found = true;
                        }
                    }
                    minfin_api.path = {};
                    for (let p in ta) {
                        minfin_api.path[p] = ta[p];
                    }
                }
                return getDrupalPath();
            }

            /*
            // Reset drupal path to individual parts from uri
            */
            function resetDrupalPath() {
                for (let v in minfin_api.data) {
                    minfin_api.path[v] = minfin_api.data[v];
                }
                return getDrupalPath();
            }

            /*
            // Get altered path for Drupal
            */
            function getDrupalPath(back) {
                var path = [];
                var ready = false;
                for (let p in minfin_api.parts) {

                    if (p > minfin_api.index && typeof (minfin_api.path[minfin_api.drupal_structure[p - minfin_api.index]]) != 'undefined') {
                        if (!ready && minfin_api.path[minfin_api.drupal_structure[p - minfin_api.index]] != '') {
                            path.push(minfin_api.path[minfin_api.drupal_structure[p - minfin_api.index]]);
                        } else {
                            ready = true;
                        }
                    }
                    else if (typeof (minfin_api.parts[p]) != 'undefined' && (minfin_api.parts[p] != '' || p < 3) && minfin_api.parts[p].indexOf('&') != 0 && minfin_api.parts[p].indexOf('?') != 0) {
                        path.push(minfin_api.parts[p]);
                    } else {
                        //console.log('empty', p);
                    }
                }
                if (back) {
                    path.pop();
                }
                path = path.join('/') + '/';
                return encodeURI(path);
            }

            /*
            // Return querystring
            */
            function addQuery(list, all) {
                var query = '';
                if (list) {
                    // prepare list
                    if (typeof (all) == 'undefined') all = false;
                    if (typeof (list) != 'object') list = [list];
                    // start query path
                    var chr = queryParams['pad'] ? '&' : '?';
                    // add existing params
                    if (all) {
                        for (let qp in queryParams) {
                            if (qp != 'pad' && qp != 'category' && qp != 'graph') {
                                query += chr + qp + '=' + queryParams[qp];
                                chr = '&';
                            }
                        }
                    }
                    // add reserved graph from list
                    if (list.indexOf('all') > -1 || list.indexOf('graph') > -1) {
                        for (let cn in chart_navigation) {
                            for (let n in chart_navigation[cn]) {
                                if (chart_navigation[cn][n].hide) {
                                    query += chr + 'graph=' + chart_navigation[cn][n].type;
                                    chr = '&';
                                }
                            }
                        }
                    }
                    // add reserved category from list
                    if ((list.indexOf('all') > -1 || list.indexOf('category') > -1) && minfin_api.select != '') {
                        // get selected item name
                        query += chr + 'category=' + encodeURI(minfin_api.select);
                        chr = '&';
                    }
                    // finally add anchorlink
                    if ((list.indexOf('all') > -1 || list.indexOf('#') > -1) && minfin_api.anchor != '') {
                        // is now setting in minfin_api
                        // query += '#' + encodeURI(minfin_api.anchor);
                    }
                }
                return query;
            }

            /*
            // Copy altered path for Drupal including querystring
            */
            function copyDrupalPath() {
                resetDrupalPath();
                var path = getDrupalPath() + addQuery('all', true);
                return path;
            }

            /*
            // Get path for API
            */
            function getApiPath(apitype) {
                var path = [];
                if (typeof (apitype) == 'undefined') {
                    for (var p in minfin_api.path) {
                        path.push(minfin_api.path[p]);
                    }
                    return '/' + path.join('/');
                } else {
                    switch (apitype) {
                        case 'rijksfinancien_triple':
                        case 'rijksfinancien_single':
                            /*
                            // TEST LOCAL
                            // IMPORTANT NOTICE
                            */
                            // /tracking/ is for test purpose only!
                            minfin_api.api_url[0] = apitype == 'rijksfinancien_triple' ? '/json/v2/triple' : '/json/single';
                            //console.log(minfin_api);
                            for (let p in minfin_api.api_url) {

                                var part = minfin_api.map[minfin_api.path[minfin_api.api_url[p]]] ? minfin_api.map[minfin_api.path[minfin_api.api_url[p]]] : minfin_api.path[minfin_api.api_url[p]];
                                var part2 = minfin_api.map[minfin_api.path[minfin_api.api_url[p]]] ? minfin_api.map[minfin_api.path[minfin_api.api_url[p]]] : minfin_api.path[minfin_api.api_url[p]];
                                if (!parseInt(p)) { // domain
                                    path.push(minfin_api.api_url[p]);
                                }
                                else if (part) { // part
                                    path.push(part);
                                }
                            }
                            return encodeURI(minfin_api.api_root + path.join('/'));
                            break;
                        default:
                            console.warn('Wrong api type ' + apitype + 'specified for getApiPath');
                            return false;
                            break;
                    }
                }
            }

            /*
            // Find array object by key
            */
            function findObjectByKey(array, key, value) {
                for (let i = 0; i < array.length; i++) {
                    if (array[i][key] === value) {
                        return array[i];
                    }
                }
                return null;
            }

            /*
            // Sort array by value
            */
            function sortArray(a, b) {
                var lst = false;
                if (sortVal[2] && a['title'] && sortVal[2] == a['title']) {
                    lst = 1;
                }
                if (sortVal[2] && b['title'] && sortVal[2] == b['title']) {
                    lst = 2;
                }
                a = a[sortVal[0]];
                b = b[sortVal[0]];
                if (!a) {
                    a = 0;
                }
                if (!b) {
                    b = 0;
                }
                if (!isNaN(a)) {
                    a = parseFloat(a);
                }
                if (!isNaN(b)) {
                    b = parseFloat(b);
                }
                if (sortVal[1] == 'desc') {
                    if (lst) {
                        return lst == 1 ? 1 : -1;
                    }
                    return a < b ? 1 : -1;
                }
                else if (sortVal[1] == 'asc') {
                    if (lst) {
                        return lst == 1 ? -1 : 1;
                    }
                    return a > b ? 1 : -1;
                }
                else if (sortVal[1] == 'reversed') {
                    if (lst) {
                        return 1;
                    }
                    return -1;
                }
                else {
                    if (lst) {
                        return 1;
                    }
                    return 0;
                }
            }

            /*
            // Update chart sizes on window change
            */
            function resize_graph(delay) {
                if (typeof (delay) == 'undefined') delay = 0;
                sluit_popup(true, false);
                var newh = 0;
                for (let c in charts) {
                    if (charts[c]) {
                        var chart = document.getElementById(charts[c].domId);
                        // resize container
                        var bb = document.getElementById(chart.id).getBoundingClientRect();
                        var p = document.getElementById(chart.id).parentElement;
                        if (bb.height > newh) newh = bb.height;
                        //p.style.minHeight = newh + 'px';

                        var chartId = parseInt(document.getElementById(charts[c].domId).getAttribute('chartId'));
                        var bbox = chart.getBoundingClientRect();
                        var max = Math.min(bbox.width, bbox.height);
                        if (discs[chartId]) {
                            discs[chartId].radius = max * 0.27;
                        }
                        if (labels[chartId]) {
                            var m = max / 460;
                            var shift = minfin_data[chartId].meta.titlerow && minfin_data[chartId].meta.titlerow[1] && minfin_data[chartId].meta.titlerow[1] != '' ? 0 : 15;
                            if (labels[chartId].titlerow1) {
                                var val = labels[chartId].titlerow1.text;
                                labels[chartId].titlerow1.text = val + ' ';
                                labels[chartId].titlerow1.fontSize = (m * 125) + '%';
                                labels[chartId].titlerow1.y = (m * -(42 - shift));
                                labels[chartId].titlerow1.align = "center";
                                if (val != ' ') setTimeout(function (chartId, val) { labels[chartId].titlerow1.text = val; }, 100, chartId, val);
                            }
                            if (labels[chartId].titlerow2) {
                                var val = labels[chartId].titlerow2.text;
                                labels[chartId].titlerow2.text = val + ' ';
                                labels[chartId].titlerow2.fontSize = (m * 125) + '%';
                                labels[chartId].titlerow2.y = (m * -14);
                                labels[chartId].titlerow2.align = "center";
                                if (val != ' ') setTimeout(function (chartId, val) { labels[chartId].titlerow2.text = val; }, 100, chartId, val);
                            }
                            if (labels[chartId].value) {
                                var val = labels[chartId].value.text;
                                labels[chartId].value.text = val + ' ';
                                labels[chartId].value.fontSize = (m * 125) + '%';
                                labels[chartId].value.y = (m * (15 - shift));
                                labels[chartId].value.align = "center";
                                if (val != ' ') setTimeout(function (chartId, val) { labels[chartId].value.text = val; }, 100, chartId, val);
                            }
                            if (labels[chartId].percentage) {
                                var val = labels[chartId].percentage.text;
                                labels[chartId].percentage.text = val + ' ';
                                labels[chartId].percentage.fontSize = (m * 100) + '%';
                                labels[chartId].percentage.y = (m * (40 - shift));
                                labels[chartId].percentage.align = "center";
                                if (val != ' ') setTimeout(function (chartId, val) { labels[chartId].percentage.text = val; }, 100, chartId, val);
                            }
                            if (labels[chartId].divider) {
                                var val = labels[chartId].divider.text;
                                labels[chartId].divider.text = val + ' ';
                                labels[chartId].divider.fontSize = (80) + '%';
                                if (val != ' ') setTimeout(function (chartId, val) { labels[chartId].divider.text = val; }, 100, chartId, val);
                            }
                        }
                        // set bar totals
                        if (typeof (charts[c].bartotals) != 'undefined' && charts[c].bartotals) {
                            setTimeout(function (chartId) {
                                barTotals(chartId);
                            }, delay, chartId);
                        }

                        if (labels[chartId]) {
                            //labels[chartId].value.text = labels[chartId].value.text;
                        }
                        // adjust chart top
                        setGraphTop(chartId);
                    }
                }
            };

            /*
            // adjust graph top relative to sister graph
            */
            function setGraphTop(chartId) {
                if (minfin_data[chartId].options && minfin_data[chartId].options['settop']) {
                    $('#' + minfin_data[chartId].options['target']).css('margin-top', 0);
                    var top = parseInt($('#' + minfin_data[chartId].options['settop']).offset().top);
                    var mytop = parseInt($('#' + minfin_data[chartId].options['target']).offset().top);
                    var dif = $(window).width() < 768 ? 0 : top - mytop;
                    $('#' + minfin_data[chartId].options['target']).css('margin-top', dif);
                }
            }

            /*
            // print bar totals above bars
            */
            function barTotals(chartId) {

                // get totals
                var newtotals = [];
                var id = -1;
                for (let c = 0; c < series.length; c++) {
                    if (series[c].chartId == chartId) {
                        id = c;
                        var barid = 0;
                        for (var b in series[c]._dataItems._values) {
                            var dc = series[c]._dataItems._values[b]._dataContext;
                            var cc = series[c]._dataItem.component;
                            if (!newtotals[barid]) newtotals[barid] = 0;
                            if (!(typeof (cc.extra) != 'undefined' && cc.extra.indexOf('minus') > -1)) {
                                if (dc[cc.dataFields.valueY]) {
                                    newtotals[barid] += dc[cc.dataFields.valueY];
                                }
                            }
                            if (labels[chartId] && labels[chartId].bar_totals && labels[chartId].bar_totals[barid]) {
                                var label = labels[chartId].bar_totals[barid];
                            }
                            barid++;
                        }
                    }
                }

                // put in labels

                for (let id = 0; id < series.length; id++) {
                    if (series[id].chartId == chartId) {
                        if (series[id] && series[id]._dataItems && series[id]._dataItems._values) {
                            var topSlices = series[id]._dataItems._values; //.sprites[0].properties)
                            if (labels[chartId] && labels[chartId].bar_totals) {
                                for (let l in labels[chartId].bar_totals) {
                                    var label = labels[chartId].bar_totals[l];
                                    if (topSlices[l].sprites.length) {
                                        //var val = charts[chartId].barValues ? label.text : format_number(topSlices[l].values.valueY.stack + topSlices[l].values.valueY.value, minfin_data[chartId].meta);
                                        var val = charts[chartId].barValues ? label.text : format_number(newtotals[l], minfin_data[chartId].meta);
                                        label.text = '';
                                        label.fontSize = 14;
                                        label.align = "center";
                                        label.x = 0;
                                        if (!label.wait && val != '' && labels[chartId].bar_totals) {
                                            setTimeout(function (label, chartId, val, l) {
                                                label.text = val;
                                                counter++;
                                                var x = labels[chartId].bar_totals ? labels[chartId].bar_totals.length : 0;
                                                label.x = am4core.percent((parseInt(l) * (100 / x)) + (0.5 * (100 / labels[chartId].bar_totals.length)));
                                            }, 100, label, chartId, val, l);
                                        }
                                        if (!charts[chartId].barValues) label.y = topSlices[l].sprites[0].realY - 2;
                                        if (newtotals[l] < 0) {
                                            label.y += 20;
                                        }
                                    }
                                    label.wait = false;
                                }
                            }
                        }
                    }
                }
            }
            var counter = 100;

            /*
            // select item in selectbox
            */
            function setSelect(id, value) {
                var dropDown = document.getElementById(id);
                var lastoption = 0;
                for (let o = 0; o < dropDown.options.length; o++) {
                    //console.log(dropDown.options[o].value, value)
                    lastoption = dropDown.options[o].value
                    if (dropDown.options[o].value == value) {
                        dropDown.options[o].selected = true;
                    }
                }
                return lastoption;
            }

            /*
            // select item in selectbox
            */
            function gotoVisual(e) {
                setDrupalPath('year', document.getElementById('select_year').value);
                setDrupalPath('phase', document.getElementById('select_phase').value);
                setDrupalPath('vuo', document.getElementById('select_vuo').value);
                //console.log('goto', this, e, getDrupalPath() + addQuery(['graph', 'category', '#']));
                location.href = getDrupalPath() + addQuery(['graph', 'category', '#'], 'all');
            }

            /*
            // pre initialisation
            */
            // oud: https://www.rijksfinancien.nl/json/single
            // path https://rijksfinancien.acceptatie.indicia.nl/
            // oApi = ['https://www.rijksfinancien.nl/json/single', 'phase', 'vuo', 'year', 'chapter', 'article', 'sub1', 'sub2', 'sub3', 'sub4'];
            //minfin/last_phase
            var minfin_api = {
                api_exceptions: ['file://', 'localhost', 'dev.3po.nl'],
                api_root: 'https://rijksfinancien.acceptatie.indicia.nl',
                anchor: 'page-title',
                drupal_structure: ['visuals', 'year', 'phase', 'vuo', 'chapter', 'article', 'sub1', 'sub2', 'sub3', 'sub4'],
                api_url: ['/json/triple', 'year', 'phase', 'vuo', 'chapter', 'article', 'sub1', 'sub2', 'sub3', 'sub4'],
                parts: [],
                data: {},
                select: '',
                path: {},
                index: false,
                options: {},
                map: {
                    'corona': 'C',
                    'jaarverslag': 'JV',
                    'jaar verslag': 'JV',
                    'annual report': 'JV',
                    'begroting': 'OWB',
                    'uitgaven': 'U',
                    'ontvangsten': 'O',
                    'verplichtingen': 'V',
                    'suppletoire1': 'O1',
                    'suppletoire2': 'O2',
                    'nl': {
                        'C': 'corona',
                        'JV': 'jaarverslag',
                        'OWB': 'begroting',
                        'U': 'uitgaven',
                        'O': 'ontvangsten',
                        'V': 'verplichtingen',
                        'O1': 'suppletoire1',
                        'O2': 'suppletoire2'
                    }
                }
            }
            var api_data = [{ "2020": 12004809, "B": 16004809, "title": "Veiligheid en Justitie", "identifier": "VI", "link": "", "children": [] },
            { "2020": 9349553, "B": 7349553, "title": "Buitenlandse Zaken", "identifier": "V", "link": "file:///Users/Mek/Dropbox/textinfo/amcharts4/examples/javascript/donut-chart/index.html?pad=/visuals/[2016]/JV/U/V/", "children": [] },
            { "2020": 3323566, "B": 3123566, "title": "Financiën", "identifier": "IXB", "link": "file:///Users/Mek/Dropbox/textinfo/amcharts4/examples/javascript/donut-chart/index.html?pad=/visuals/[2016]/JV/U/IXB/", "children": [{ "title": "Belastingen", "identifier": "1", "amount": 3323566 }] },
            { "2020": 158243, "B": 258243, "title": "Binnenlandse Zaken en Koninkrijksrelaties", "identifier": "VII", "link": "file:///Users/Mek/Dropbox/textinfo/amcharts4/examples/javascript/donut-chart/index.html?pad=/visuals/[2016]/JV/U/VII/", "children": [] }];


            // get structure right away
            getDrupalParts();
            // start listners
            window.addEventListener('resize', resize_graph);

            /*
            // Populate navigation bar
            */
            var chart_navigation = [];

            function navigation(id, charts) {

                // preset carousel link
                chart_navigation[id] = charts;
                var carousel = document.querySelector('#' + id + ' #chart_nav_carousel');

                var txt = -1;
                for (let c in charts) {
                    var chart = charts[c].id ? document.getElementById(charts[c].id) : false;
                    if (chart) {
                        if (!charts[c].hide) {
                            chart.style.left = 'auto';
                            chart.style.display = 'block';
                            txt = -2;
                        }
                        else if (carousel) {
                            //chart.style.left = '-200%';
                            chart.style.display = 'none';
                            if (txt == -2) {
                                txt = c;
                            }
                        }
                    }
                }
                if (txt == -2) {
                    txt = 0;
                }
                if (carousel) {
                    carousel.innerHTML = ''; //txt > -1 && charts[txt].link ? charts[txt].link : '';
                    if (txt > -1) {
                        carousel.classList.add(txt ? 'donut' : 'bar');
                        // update link
                        if (carousel.classList.contains('bar')) {
                            carousel.setAttribute('title', 'Toon staafdiagram');
                        }
                        if (carousel.classList.contains('donut')) {
                            carousel.setAttribute('title', 'Toon donut');
                        }
                        carousel.setAttribute('tabindex', 6);
                    } else {
                        carousel.style.display = 'none';
                        carousel.setAttribute('tabindex', -1);
                    }

                    // set the carousel link
                    carousel.addEventListener('click', function (e) {
                        // close popup first
                        sluit_popup(true, false);
                        // whats next + thereafter?
                        var id = e.target.parentNode.parentNode.getAttribute('id');
                        var link = e.target.innerHTML;
                        var found = -1;
                        var target = -1;
                        var next = -1;
                        for (let n in chart_navigation[id]) {
                            if (target != -1) {
                                next = n;
                            }
                            if (found != -1) {
                                target = n;
                            }
                            if (!chart_navigation[id][n].hide) {
                                found = n;
                            }
                        }
                        if (next == -1 && target == -1) {
                            next = 1;
                        }
                        if (next == -1) {
                            next = 0;
                        }
                        if (target == -1) {
                            target = 0;
                        }
                        // hide found
                        chart_navigation[id][found].hide = true;
                        if (window.jQuery) {
                            $('#' + chart_navigation[id][found].id).animate({
                                opacity: 0
                            }, 150, function () {
                                // show new
                                $(this).css('left', '-20%').css('opacity', 0).css('display', 'none');
                                $('#' + chart_navigation[id][target].id).css('left', 'auto').css('opacity', 0).css('display', '').animate({
                                    opacity: 1
                                }, 150);
                            });
                        } else {
                            var obj = document.getElementById(chart_navigation[id][found].id);
                            obj.style.left = '-200%';
                            // show new
                            var obj = document.getElementById(chart_navigation[id][target].id);
                            obj.style.left = 'auto';
                        }
                        chart_navigation[id][target].hide = false;
                        // update link
                        e.target.innerHTML = ''; //chart_navigation[id][next].link;
                        e.target.classList.remove(next ? 'bar' : 'donut');
                        e.target.classList.add(next ? 'donut' : 'bar');
                        if (e.target.classList.contains('bar')) {
                            e.target.setAttribute('title', 'Toon staafdiagram');
                        }
                        if (e.target.classList.contains('donut')) {
                            e.target.setAttribute('title', 'Toon donut');
                        }
                        // reset label positions
                        setTimeout(function () {
                            resize_graph();
                        }, 250);

                        //if (e.target.classList.contains('bar')) {
                        $('[curyear="no"]').css('display', 'none');
                        //} else {
                        //    $('[curyear="no"]').css('display', 'block');
                        //}
                    });
                    // hide carousel link if there's only one
                    if (charts.length < 2) {
                        document.querySelector('#' + id + ' #chart_nav_carousel').style.display = 'none';
                        document.querySelector('#' + id + ' #chart_nav_carousel').setAttribute('tabindex', -1);
                        document.querySelector('#' + id + ' #chart_nav_carousel').classList.add('noswitch');
                    }
                    document.querySelector('#' + id).style.display = 'block';

                    // fill selects
                    if (chart_navigation[id][0].options != 'none') {
                        callApi('rijksfinancien_selectors');
                    }

                    // reset label positions
                    setTimeout(function () {
                        resize_graph();
                    }, 2000);

                }

                // preset the back link
                var back = document.querySelector('#' + id + ' #chart_nav_return');
                if (back) {
                    back.setAttribute('tabindex', 4);
                    back.setAttribute('title', 'Terug naar vorige pagina');
                }

                if (chart_navigation[id][0].back == 'identifier') {

                } else {
                    if (!minfin_api.data.chapter) {
                        back.style.visibility = 'hidden';
                    }
                    // set the back link
                    if (back) {
                        back.addEventListener('click', function (e) {
                            location.href = getDrupalPath(true) + addQuery(['graph', '#']);
                        });
                    }
                }

                // add labels to navigation
                $('#chart_navigation select').each(function () {
                    var slct = $(this).attr('id');
                    var title = 'Selectie';
                    var tab = 1;
                    switch (slct) {
                        case 'select_year':
                            title = 'Kies jaar';
                            tab = 2;
                            break;
                        case 'select_vuo':
                            title = 'Kies uitgave of ontvangsten';
                            tab = 3;
                            break;
                        case 'select_phase':
                            title = 'Kies fase';
                            tab = 1;
                            break;
                    }
                    $(this).attr('title', title);
                    $(this).attr('tabindex', tab);
                    $(this).before('<label for="' + slct + '" title="' + title + '" style="display: none;">' + $(this).attr('id') + '</label>');
                })
            }

            /*
            // Call charts or wait for api data
            */
            var chartLoop = [];

            function minfin_chart(id, type, datatype, options) {
                //console.log(id, type, datatype, options)
                if (typeof (options.divider) == 'undifined') {
                    options.divider = 1;
                }
                if (typeof (options.multiplier) == 'undifined') {
                    options.multiplier = 1;
                }
                if (typeof (datatype) == 'object') { // we have data
                    // set titles
                    var target = typeof (options.use_api_title) == 'string' ? document.querySelector('.' + options.use_api_title) : false;
                    if (target && options.title) target.innerHTML = options.page_title ? options.page_title : options.title;
                    target = document.getElementById('legend_title');
                    if (target) target.innerHTML = options.title ? options.title : '';
                    var meta = {}
                    meta.legend_title = options.value ? options.value : '';
                    if (options.legend_title) meta.legend_title = options.legend_title;
                    if (options.quicklink) meta.quicklink = options.quicklink;
                    if (options.childlink) meta.childlink = options.childlink;
                    if (options.negatives) meta.negatives = options.negatives;
                    if (options.singlebars) meta.singlebars = options.singlebars;

                    var allBars = [];
                    var absTotal = 0;
                    var absLowest = 0;
                    var absHighest = 0;
                    var total = 0;
                    for (let d in datatype) {
                        allBars.push(datatype[d][options.category]);
                        absTotal += datatype[d][datatype[d][options.category]];
                        if (datatype[d][datatype[d][options.category]] < absLowest) {
                            absLowest = datatype[d][datatype[d][options.category]];
                        }
                        if (datatype[d][datatype[d][options.category]] > absHighest) {
                            absHighest = datatype[d][datatype[d][options.category]];
                        }
                        if (datatype[d][options.value]) {
                            if (!datatype[d].extra || datatype[d].extra.indexOf('minus') == -1) {
                                total += divide_number(datatype[d][options.value], options);
                            }
                            if (datatype[d].range && datatype.length == 1) {
                                total = datatype[d].range;
                                meta.range = datatype[d].range;
                            }
                        }
                        if (typeof (options.singlebars) != 'undefined' && options.singlebars) {
                            //datatype[d][datatype[d][options.category]] = datatype[d][options.value];
                        }
                    }

                    if (typeof (options.singlebars) != 'undefined' && options.singlebars) {
                        if (typeof (options.bars) == 'undefined') {
                            options.bars = allBars;
                        }
                        meta.legend_total = format_number(divide_number(absTotal, options), options);
                        // set lowest value
                        options.lowest = absLowest / (options.divider ? options.divider : 1);
                        options.highest = absHighest / (options.divider ? options.divider : 1);
                    } else {
                        meta.legend_total = options.total ? format_number(divide_number(options.total, options), options) : total ? format_number(total, options) : '';
                    }
                    //console.log(datatype, options)
                    _minfin_chart(id, type, datatype, options, meta);
                }
                else { // wait for api
                    var fnd = false;
                    for (let c in chartLoop) {
                        if (chartLoop[c].id == id && chartLoop[c].type == type) {
                            chartLoop[c].count++;
                            fnd = true;
                            break;
                        }
                    }
                    if (!fnd) {
                        chartLoop.push({
                            id: id,
                            type: type,
                            count: 1,
                            calls: options.calls ? options.calls : 1,
                            datatype: datatype,
                            options: options
                        });
                    }
                    // spinner
                    if (options.loader) {
                        var spinner = document.getElementById(id);
                        spinner.classList.add('spin');
                    }
                }
            }

            /*
            // Load textfile from disk
            */
            // NEW
            var csv_data = [];
            var csv_options = {};


            function containsNonLatinCodepoints(s) {
                return /[^\u0000-\u00ff]/.test(s); // ISO
            }

            function readTextFile(file, callback, options) {
                if (typeof (options) == 'undefined') options = {};
                if (typeof (options.charset) == 'undefined') options.charset = 'UTF-8';
                csv_options = options;
                var tmp_data = [];
                var rawFile = new XMLHttpRequest();
                rawFile.open("get", file, false);
                rawFile.overrideMimeType("text/html; charset=" + options.charset);
                rawFile.onreadystatechange = function () {
                    if (rawFile.readyState === 4) {
                        if ((rawFile.status === 200 || rawFile.status == 0) && rawFile.responseText) {
                            if (window.hasOwnProperty('Papa') && file.indexOf('.json') == -1) {
                                tmp_data = Papa.parse(rawFile.responseText, {
                                    header: true
                                });
                            } else if (file.indexOf('.json') > 0) {
                                tmp_data['data'] = JSON.parse(rawFile.responseText);
                            }
                            if (tmp_data.data) {
                                // for (var u in tmp_data.data) {
                                //     console.log(u, tmp_data.data[u])
                                // }
                                callback(tmp_data.data, options);

                            } else {
                                console.warn("CSV parse failed");
                            }
                        }
                        else {
                            console.warn("CSV call failed: " + rawFile.responseText);
                        }
                    }
                }
                rawFile.send(null);
            }

            function getYearsFirst(apiPath, callback, options) {

                var request = new XMLHttpRequest();
                request.addEventListener("load", function (e) {
                    var tmp_data = this.responseText;

                    try {
                        tmp_data = JSON.parse(tmp_data);
                    } catch (err) {

                        console.warn("JSON parser failed");
                        var apiresult = false;
                    }

                    if (tmp_data) {
                        var years = [];
                        var currentdefault = false;
                        var lastyear = false;
                        for (var u in tmp_data) {
                            years.push(parseInt(u));
                            lastyear = u;
                        }

                        // get years first?
                        if (typeof (options['select']) != 'undefined') {
                            for (var y in options['select']) {
                                if (options['select'][y]['type'] == 'year') {
                                    options['select'][y]['options'] = years;
                                    currentdefault = typeof (options['select'][y]['query']) != 'undefined' ? options['select'][y]['query'] : false;
                                    if (currentdefault && options.api && options.api.query && options.api.query[currentdefault]) {
                                        if (years.indexOf(parseInt(options.api.query[currentdefault])) == -1) {
                                            //options.api.query[currentdefault] = lastyear;

                                            options['select'][y]['options'].push(options.api.query[currentdefault]);
                                            options['select'][y]['options'].sort();
                                        }
                                    }
                                }
                            }
                        }
                        //console.warn("Available_year parsed successfully from API:", tmp_data);
                        readAPIFile(callback, options);
                    } else {
                        //console.warn(responsetype + " parse from "+apiPath+" failed");
                    }
                });
                request.addEventListener("error", function (e) {
                    console.warn("Api call failed from " + apiPath + ":", this.responseText);
                });
                //console.log('API call to: ', apiPath);
                request.open("GET", apiPath); // apiPath
                request.setRequestHeader("contentType", "application/json; charset=" + options.charset);
                request.send();
            }

            function readAPIFile(callback, options, reloading, skipquerystring) {
                reloading = typeof (reloading) == 'undefined' || !reloading ? false : true;
                skipquerystring = typeof (skipquerystring) == 'undefined' || !skipquerystring ? false : true;
                var apiquerystring = '';
                // api thingies
                if (options.api && !reloading) {
                    if (typeof (options.api['trigger']) == 'string' || typeof (options.api['handling']) == 'string') {
                        minfin_api.drupal_structure = [options.api['trigger']];
                        if (typeof (options.api['structure']) == 'object' && options.api['structure'].length) {
                            for (var st in options.api['structure']) {
                                minfin_api.drupal_structure.push(options.api['structure'][st]);
                            }
                            if (typeof (minfin_api.path[options.api['trigger']]) == 'undefined') {
                                minfin_api.path[options.api['trigger']] = options.api['trigger'];
                            }
                            if (typeof (options.api['query']) == 'string') {
                                var params = new URLSearchParams(window.location.search)
                                for (var param of params) {
                                    if (param[0] == options.api['query']) {
                                        options.api['default'] = [param[1]];
                                    }
                                }
                            } else if (typeof (options.api['query']) == 'object') { // o.a. verzelfstandigingen
                                var params = new URLSearchParams(window.location.search)
                                for (var q in options.api['query']) {
                                    for (var param of params) {
                                        if (param[0] == q && !skipquerystring) {
                                            options.api['query'][q] = param[1];
                                        }
                                    }
                                    if (options.api['query'][q] != '') {
                                        if (apiquerystring != '') {
                                            apiquerystring += "&";
                                        }
                                        apiquerystring += q + '=' + options.api['query'][q];
                                    }
                                }
                            }

                            if (typeof (options.api['default']) != 'object' && options.api['default']) {
                                options.api['default'] = [options.api['default']];
                            }

                            if (typeof (options.api['default']) == 'object' && options.api['default'].length) {
                                for (var st in options.api['structure']) {
                                    if (typeof (minfin_api.path[options.api['structure'][st]]) == 'undefined' && typeof (options.api['default'][st]) != 'undefined') {
                                        //console.log(options.api['structure'][st], options.api['default'][st])
                                        minfin_api.path[options.api['structure'][st]] = options.api['default'][st];
                                    }
                                }
                            }
                        }
                        if (options.select && options.select.length) {
                            for (var os in options.select) {
                                var selectname = 'available_' + options.select[os]['type'] + 's';
                                if (!minfin_api[selectname]) {
                                    minfin_api[selectname] = {};
                                }
                                if (typeof (options.select[os]['type']) == 'string' && options.select[os]['options']) {
                                    minfin_api[selectname][options.api['trigger']] = options.select[os]['options'];
                                }
                            }
                        }
                    }
                    // get parts from url
                    getDrupalParts();
                }
                // set anchor for title
                if (typeof (options.anchor) == 'string') {
                    am4core.settings({
                        anchor: options.anchor,
                    });
                }
                var apiroot = typeof (options['api']) != 'undefined' && typeof (options['api']['domain']) == 'string' ? options['api']['domain'] : minfin_api.api_root;
                // SET TEST API FOR LOCAL USE AT TOP OF DOCUMENT
                if (testlocal && testapi) {
                    apiroot = testapi;
                }
                var apitype = typeof (options['api']) != 'undefined' && typeof (options['api']['type']) == 'string' ? options['api']['type'] : '';
                var apiurl = typeof (options['api']) != 'undefined' && typeof (options['api']['url']) == 'string' ? options['api']['url'] : minfin_api.api_url[0];

                // get years first?
                var getyearsfirst = -1;
                if (typeof (options['select']) != 'undefined') {
                    for (var y in options['select']) {
                        if (options['select'][y]['type'] == 'year' && typeof (options['select'][y]['api']) != 'undefined' && options['select'][y]['api']) {
                            if(options['select'][y]['api'].indexOf('/') == 0) {
                                apiurl = apiurl + options['select'][y]['api'];
                            } else {
                                apiurl = apiurl + '/available_years/' + options['select'][y]['api'];
                            }
                            getyearsfirst = y;
                            options['select'][y]['api'] = false;
                        }
                    }
                }
                if(getyearsfirst == -1 && typeof (options['api']) != 'undefined' && typeof (options['api']['path']) == 'string') {
                    apiurl = options['api']['path'];
                }

                if (getyearsfirst == -1 && ((typeof (options['api']) != 'undefined' || typeof (options['api']['url']) != 'undefined') && (typeof (options['api']['usepath']) == 'undefined' || options['api']['usepath']))) {
                    // if ther's a path, use it instead of the trigger path
                    if(typeof (options['api']) != 'undefined' && typeof (options['api']['path']) == 'string' && typeof (options['api']['trigger']) == 'string') {
                        var tpath = [];
                        for (var p in minfin_api.path) {
                            if(p != options['api']['trigger']) {
                                tpath.push(minfin_api.path[p]);
                            }
                        }
                        apiurl += '/' + tpath.join('/');
                    } else {
                        apiurl += getApiPath();
                    }
                }

                if (typeof (options) == 'undefined') options = {};
                if (typeof (options.charset) == 'undefined') options.charset = 'UTF-8';
                csv_options = options;
                var apiPath = apiroot + apiurl;
                if (apiquerystring != '') {
                    apiPath += '?' + apiquerystring;
                }

                if (getyearsfirst > -1) {
                    getYearsFirst(apiPath, callback, options);
                } else if (getyearsfirst && apiPath) {
                    var request = new XMLHttpRequest();
                    request.addEventListener("load", function (e) {
                        var tmp_data = this.responseText;
                        var responsetype = "Data";
                        if (apitype == 'csv' || apiurl.indexOf('csv') > -1) {
                            responsetype = "CSV";
                            if (window.hasOwnProperty('Papa')) {
                                tmp_data = Papa.parse(tmp_data, {
                                    header: true
                                });
                            } else {
                                console.warn("Papaparse missing");
                            }
                        } else if (apitype == 'json' || apiurl.indexOf('json') > -1) {
                            responsetype = "JSON";
                            try {
                                tmp_data = JSON.parse(tmp_data);

                                // exception
                                if (options.exception == 'uitgavenplafonds2') {
                                    console.warn("Uitgavenplafonds2 exception");
                                    for (var c in tmp_data['children']) {
                                        if (tmp_data['children'][c]['extra']) {
                                            tmp_data['children'][c]['title'] += ' (corona gerelateerd)';
                                        }
                                    }
                                } else if (options.exception == 'uitstel_van_betaling') {
                                    console.warn("uitstel_van_betaling exception");
                                    for (var c in tmp_data['children']) {
                                        tmp_data['children'][c]['curyear'] = true;
                                        if (tmp_data['children'][c]['extra']) {
                                            tmp_data['children'][c]['title'] += ' (prognose afstel)';
                                        }
                                    }
                                }

                                tmp_data['data'] = tmp_data;
                            } catch (err) {
                                console.warn("JSON parser failed");
                                var apiresult = false;
                            }
                        }

                        if (tmp_data.data) {
                            // for (var u in tmp_data.data) {
                            //     console.log(u, tmp_data.data[u])
                            // }
                            //console.warn(responsetype + " parsed successfully from API:", tmp_data.data, options);
                            callback(tmp_data.data, options);
                        } else {
                            //console.warn(responsetype + " parse from "+apiPath+" failed");
                        }
                    });
                    request.addEventListener("error", function (e) {
                        console.warn("Api call failed from " + apiPath + ":", this.responseText);
                    });
                    //console.log('API call to: ', apiPath);
                    request.open("GET", apiPath); // apiPath
                    request.setRequestHeader("contentType", "application/json; charset=" + options.charset);
                    request.send();
                }
            }

            /*
            // Call API
            */
            function callApi(datatype, target) {

                // TEST LOCAL
                //minfin_api.api_root = 'https://beta.rijksfinancien.nl';
                //minfin_api.api_root = 'https://rijksfinancien.acceptatie.indicia.nl';

                //console.log(datatype, target)
                if (typeof (target) == 'undefined') target = false;
                // maak path
                var apiPath = false;
                switch (datatype) {
                    case 'rijksfinancien_triple':
                    case 'rijksfinancien_single':
                        if (minfin_api.index) {
                            // these api calls use url data
                            // so it must be available via url
                            if (!minfin_api.data.year || !minfin_api.data.phase || !minfin_api.data.vuo) {
                                // get missing info first
                                apiPath = false;
                                //console.log(datatype)
                                callApi('rijksfinancien_default', datatype);
                            } else {
                                if (minfin_api.data.year && minfin_api.data.phase && minfin_api.data.vuo) {
                                    apiPath = getApiPath(datatype);
                                }
                            }
                        } else {
                            console.warn('No api target specified. Add year, phase and vuo to url');
                        }
                        break;
                    case 'rijksfinancien_default':
                        apiPath = minfin_api.api_root + '/json/minfin/last_phase';
                        break;
                    case 'rijksfinancien_selectors':
                        apiPath = minfin_api.api_root + '/json/minfin/available_phases';
                        break;
                }
                // console.log('api', apiPath);
                // do call
                if (apiPath) {
                    var request = new XMLHttpRequest();
                    request.addEventListener("load", function (e) {
                        try {
                            var apiresult = JSON.parse(this.responseText);
                            //console.log(this.responseText)
                            if (this.datatype == "rijksfinancien_triple") {

                                //apiresult = jsnn;
                                //console.log(target, this.datatype, apiresult)

                                // seek corresponding groups to add patterns
                                let groups = {};
                                let tempgroups = {};
                                for (let i in apiresult) {
                                    if (typeof (apiresult[i]['children'] != 'undefined')) {
                                        for (let ii in apiresult[i]['children']) {
                                            if (typeof (tempgroups[apiresult[i]['children'][ii]['group']]) == 'undefined') {
                                                tempgroups[apiresult[i]['children'][ii]['group']] = 0;
                                            } else {
                                                tempgroups[apiresult[i]['children'][ii]['group']] = 1;
                                            }
                                        }
                                        for (i in tempgroups) {
                                            if (tempgroups[i] > 0) {
                                                groups[i] = 1;
                                            }
                                        }
                                    }
                                }
                                // set patterns
                                for (let i in apiresult) {
                                    if (typeof (apiresult[i]['children'] != 'undefined')) {
                                        for (let ii in apiresult[i]['children']) {
                                            if (typeof (apiresult[i]['children'][ii]['group']) != 'undefined' && groups[apiresult[i]['children'][ii]['group']] == 1) {
                                                apiresult[i]['children'][ii]['extra'] = 'pattern';
                                            }
                                        }
                                    }
                                }
                            }
                        }
                        catch (err) {
                            console.warn("Api parser failed");
                            var apiresult = false;
                        }
                        if (apiresult) {
                            //console.log(apiresult);
                            if (this.datatype == 'rijksfinancien_selectors') {
                                // set navigatie selectors (year we do later)
                                var dropDown = document.getElementById('select_phase');
                                var dropDownV = document.getElementById('select_vuo');
                                var options = '';
                                var optionsV = '';
                                var phs = { // phase map
                                    'jv': 'Jaarverslag',
                                    'o1': '1e Suppletoire',
                                    'o2': '2e Suppletoire',
                                    'owb': 'Begroting'
                                }
                                var phsu = { // phase map fur url
                                    'jv': 'jaarverslag',
                                    'o1': 'suppletoire1',
                                    'o2': 'suppletoire2',
                                    'owb': 'begroting'
                                }
                                for (let p in apiresult) {
                                    //console.log(p, phs[p]);
                                    var item = typeof (phs[p]) == 'undefined' ? 'Onbekend' : phs[p];
                                    var itemval = typeof (phsu[p]) == 'undefined' ? 'onbekend' : phsu[p];
                                    options += '<option value="' + itemval + '">' + item + '</option>';
                                }
                                var vuo = {
                                    'U': 'Uitgaven',
                                    'O': 'Ontvangsten',
                                    'V': 'Verplichtingen'
                                }
                                for (let v in vuo) {
                                    optionsV += '<option value="' + vuo[v].toLowerCase() + '">' + vuo[v] + '</option>';
                                }
                                dropDown.innerHTML = options;
                                dropDown.addEventListener('change', gotoVisual);
                                dropDownV.innerHTML = optionsV;
                                dropDownV.addEventListener('change', gotoVisual);
                                minfin_api['available_years'] = apiresult;

                            } else if (this.datatype == 'rijksfinancien_default') {
                                // preload
                                // add missing year
                                if (!minfin_api.data['year'] && apiresult.year) {
                                    minfin_api.data['year'] = apiresult.year;
                                    minfin_api.path['year'] = apiresult.year;
                                    minfin_api.parts.push(apiresult.year);
                                }
                                // add missing phase
                                if (!minfin_api.data['phase'] && apiresult.phase) {
                                    minfin_api.data['phase'] = apiresult.phase.toUpperCase();
                                    minfin_api.path['phase'] = apiresult.phase.toUpperCase();
                                    minfin_api.parts.push(apiresult.phase.toUpperCase());
                                }
                                // never above latest year/phase
                                if (parseInt(minfin_api.data['year']) > parseInt(apiresult.year)) {
                                    minfin_api.data['year'] = apiresult.year;
                                    minfin_api.path['year'] = apiresult.year;
                                    setDrupalPath('year', apiresult.year);
                                    minfin_api.data['phase'] = apiresult.phase.toUpperCase();
                                    minfin_api.path['phase'] = apiresult.phase.toUpperCase();
                                    setDrupalPath('phase', apiresult.phase.toUpperCase());
                                }
                                // default uitgaven
                                if (!minfin_api.data['vuo']) {
                                    minfin_api.data['vuo'] = 'U';
                                    minfin_api.path['vuo'] = 'U';
                                    minfin_api.parts.push('U');
                                }
                                callApi(target);
                            } else if (this.status == 404 && minfin_api.data.chapter) {
                                setDrupalPath('chapter', '');
                                location.href = getDrupalPath() + addQuery(['graph', '#']);
                            } else {
                                //console.log(chartLoop)
                                // actual data
                                for (let cl in chartLoop) {
                                    if (chartLoop[cl].datatype == this.datatype) {
                                        switch (this.datatype) {
                                            case 'rijksfinancien_triple':
                                            case 'rijksfinancien_single':
                                                // minfin specific api
                                                // https://www.rijksfinancien.nl/doc/api#/CSV%20SingleArray/SingleArray
                                                // https://www.rijksfinancien.nl/doc/api/json/single/{phase}/{vuo}/{year}/{chapter}/{article}/{sub1}

                                                // get api year
                                                var year = minfin_api.data.year;
                                                if (chartLoop[cl].options.value == 'autoyear') {
                                                    chartLoop[cl].options['autoyear'] = this.datatype == 'rijksfinancien_single' && this.target ? false : true;
                                                    chartLoop[cl].options.value = year;
                                                }

                                                // get map value for value and category
                                                var value = chartLoop[cl].options.value ? chartLoop[cl].options.value : 'value';
                                                var category = chartLoop[cl].options.category ? chartLoop[cl].options.category : 'category';
                                                chartLoop[cl].options.datatype = this.datatype;
                                                // get meta and normal data
                                                var meta = {}
                                                chartLoop[cl].data = [];


                                                // get single api title
                                                var barTitle = this.target;
                                                if (this.datatype == 'rijksfinancien_single' && this.target) {
                                                    for (let m in minfin_api.map) {
                                                        if (minfin_api.map[m] == barTitle) {
                                                            barTitle = cap(m);
                                                            break;
                                                        }
                                                    }
                                                }

                                                // set number of bars
                                                chartLoop[cl].options.bars = [];
                                                if (this.datatype == 'rijksfinancien_single' && this.target) {
                                                    chartLoop[cl].options.bars.push(barTitle);
                                                } else {
                                                    for (let y in apiresult) {
                                                        chartLoop[cl].options.bars.push(y);
                                                    }
                                                }

                                                var chartTitle = '';

                                                // fill data from current year
                                                var result = apiresult[value];
                                                for (let r in result) {
                                                    // back title
                                                    if (r == 'back_title') meta.previous = result[r];
                                                    if (r == 'title') {
                                                        chartTitle = result[r];
                                                        if (chartTitle == '') {
                                                            chartTitle = result['back_title'] ? result['back_title'] : '?'; //'replacement';
                                                        }
                                                    } else if (r != 'children') {
                                                        meta[r] = result[r];
                                                    }
                                                    else {
                                                        var collect = [];
                                                        for (let c in result[r]) {
                                                            while (collect.indexOf(result[r][c]['title']) > -1) {
                                                                result[r][c]['title'] += ' ';
                                                            }
                                                            collect.push(result[r][c]['title']);

                                                            var d = {}
                                                            for (let oy2 in apiresult) {
                                                                d[oy2] = 0
                                                            }
                                                            var title = '';
                                                            var identifier = '';
                                                            if (result[r][c]['title'] == '') {
                                                                result[r][c]['title'] = chartTitle; // replacement
                                                            }
                                                            if (typeof (result[r][c]['description']) != 'undefined') {
                                                                result[r][c]['description_' + value] = result[r][c]['description'];
                                                            }

                                                            var newlink = "";
                                                            for (let i in result[r][c]) {
                                                                var key = i == 'value' || i == 'amount' ? value : i == 'name' || i == 'title' ? category : i;

                                                                if ((i == 'value' || i == 'amount') && this.datatype == 'rijksfinancien_single' && this.target) {
                                                                    key = barTitle;
                                                                }

                                                                if (i == 'children') {
                                                                    for (let cc in result[r][c][i]) {
                                                                        if (typeof (result[r][c][i][cc]['title']) != 'undefined' && result[r][c][i][cc]['title'] == '') {
                                                                            result[r][c][i][cc]['title'] = result[r][c]['title']; //'replacement';
                                                                        }
                                                                    }
                                                                }

                                                                d[key] = result[r][c][i];
                                                                // add link with new identifier
                                                                if (key == 'identifier' && result[r][c][i] && result[r][c].children && Object.keys(result[r][c].children).length) {
                                                                    d['link'] = getDrupalPath().replace('/' + year + '/', '/[' + year + ']/') + result[r][c][i] + '/';
                                                                }
                                                                // fix relative links without domain
                                                                if (d['link'] && d['link'].indexOf('/') == 0) {
                                                                    newlink = "";
                                                                    for (var p in minfin_api.parts) {
                                                                        if (p <= minfin_api.index + 3 || minfin_api.parts[p].indexOf('?') == 0) { //} || minfin_api.parts[p].indexOf('&') == 0) {
                                                                            if (newlink != '') {
                                                                                newlink += '/';
                                                                            }
                                                                            newlink += minfin_api.parts[p];
                                                                        } else if (p == minfin_api.index + 4) {
                                                                            newlink += '{identifier}';
                                                                        }
                                                                    }
                                                                    d['link'] = newlink.replace('{identifier}', d['link']);
                                                                }
                                                                if (i == 'title') title = result[r][c][i];
                                                                if (i == 'identifier') identifier = result[r][c][i];
                                                            }
                                                            if (!d['value']) {
                                                                d['value'] = value;
                                                            }
                                                            d['curyear'] = true;

                                                            // add data from other years
                                                            for (let y in apiresult) {
                                                                d[y] = 0;
                                                                for (let rr in apiresult[y]['children']) {
                                                                    if (identifier == apiresult[y]['children'][rr].identifier) {
                                                                        //console.log(title, apiresult[y]['children'][rr].title)
                                                                        d[y] = apiresult[y]['children'][rr]['amount'] ? apiresult[y]['children'][rr]['amount'] : 0;
                                                                        if (typeof (apiresult[y]['children'][rr]['description']) != 'undefined') {
                                                                            d['description_' + y] = apiresult[y]['children'][rr]['description'];
                                                                        }
                                                                        //break;
                                                                    }
                                                                }
                                                            }

                                                            // add missing data from other years
                                                            for (let y in apiresult) {
                                                                //console.log(y, apiresult[y])
                                                            }

                                                            //console.log(d)

                                                            //d['year_identifier'] = chartLoop[cl].options.bars[c];
                                                            chartLoop[cl].data.push(d);
                                                        }
                                                    }
                                                }
                                                //console.log(year, chartLoop[cl].data)

                                                // add missing data from other years
                                                var result = apiresult[value];
                                                for (let oy in apiresult) {
                                                    if (oy != value) {
                                                        for (let r in apiresult[oy]) {
                                                            if (r == 'children') {
                                                                for (let child in apiresult[oy][r]) {
                                                                    var collect = {};
                                                                    for (let oy2 in apiresult) {
                                                                        collect[oy2] = 0
                                                                    }
                                                                    for (let co in apiresult[oy][r][child]) {
                                                                        //console.log('----', co, apiresult[oy][r][child][co])
                                                                        collect[co] = apiresult[oy][r][child][co];
                                                                        //let tidentifier = apiresult[oy][r][thec]['identifier'];
                                                                        //let ttitle = apiresult[oy][r][thec]['title'];
                                                                        if (co == 'description') {
                                                                            collect['description_' + oy] = collect[co];
                                                                        }
                                                                    }
                                                                    if (collect['identifier']) {
                                                                        var ident = collect['identifier']
                                                                        var found = false;
                                                                        // search for corresponding row
                                                                        for (let cld in chartLoop[cl].data) {
                                                                            if (chartLoop[cl].data[cld]['identifier'] == ident) {
                                                                                found = true;
                                                                                chartLoop[cl].data[cld][oy] = collect['amount'];
                                                                                if (typeof (collect['description_' + oy]) != 'undefined') {
                                                                                    chartLoop[cl].data[cld]['description_' + oy] = collect['description_' + oy];
                                                                                }
                                                                            }
                                                                        }
                                                                        if (!found) {
                                                                            //console.log('nieuw', oy, collect['amount'], collect)
                                                                            collect[oy] = collect['amount'];
                                                                            // fix relative path
                                                                            if (collect.link && collect.link.indexOf('/') == 0) {
                                                                                collect['link'] = newlink.replace('{identifier}', collect['link']);
                                                                            }
                                                                            chartLoop[cl].data.push(collect);
                                                                        }
                                                                        //console.log('----', collect)
                                                                    }
                                                                }
                                                            }
                                                        }
                                                    }
                                                }

                                                // make all titles unique
                                                for (let cld in chartLoop[cl].data) {
                                                    var ttl = chartLoop[cl].data[cld]['title'];
                                                    for (let cldi in chartLoop[cl].data) {
                                                        if (cldi != cld && chartLoop[cl].data[cldi]['title'] == ttl) {
                                                            chartLoop[cl].data[cldi]['title'] += " ";
                                                        }
                                                    }
                                                }

                                                // calculate totals
                                                var totals = {};
                                                for (let b in chartLoop[cl].options.bars) {
                                                    var identifier = chartLoop[cl].options.bars[b];
                                                    totals[identifier] = 0;
                                                    var y = chartLoop[cl].options.bars[b];
                                                    for (let d in chartLoop[cl].data) {
                                                        totals[identifier] += divide_number(chartLoop[cl].data[d][y], chartLoop[cl].options);
                                                    }
                                                }
                                                meta.year_totals = totals;
                                                meta.current_year = minfin_api.data.year;

                                                // set meta.previous
                                                if (!meta.previous) meta.previous = 'een niveau hoger';
                                                if (meta.previous && meta.previous != '') {
                                                    var link = document.getElementById('chart_nav_return');
                                                    if (link) link.innerHTML = 'Terug naar ' + meta.previous;
                                                    if (link) link.setAttribute('title', 'Terug naar ' + meta.previous);
                                                }

                                                // set link to document
                                                if (meta.kamerstuk && meta.kamerstuk != null && meta.kamerstuk.length) {
                                                    var kamerlink = document.getElementById('chart_nav_paper');
                                                    kamerlink.setAttribute('tabindex', 5);
                                                    kamerlink.href = meta.kamerstuk;
                                                    kamerlink.style.display = 'block';
                                                }

                                                meta.datatype = this.datatype;
                                                meta.autoyear = chartLoop[cl].options.autoyear ? chartLoop[cl].options.autoyear : false;
                                                meta.title = chartLoop[cl].options.title ? chartLoop[cl].options.title : chartTitle;
                                                if (chartLoop[cl].options.quicklink && !isTouchDevice()) meta.quicklink = chartLoop[cl].options.quicklink;
                                                meta.childlink = chartLoop[cl].options.childlink ? chartLoop[cl].options.childlink : false;

                                                api_data[chartLoop[cl].datatype] = result;
                                                chartLoop[cl]['meta'] = meta;

                                                if (this.datatype == 'rijksfinancien_single' && this.target) {
                                                    chartLoop[cl].options.value = barTitle;
                                                    chartLoop[cl].meta['current_year'] = barTitle;
                                                    meta.title = '';
                                                }

                                                // add color exception
                                                if (!minfin_api.data.chapter) {
                                                    var except_colors = [];
                                                    for (let i in chartLoop[cl].data) {
                                                        var c = ministerie_identifiers[chartLoop[cl].data[i]['identifier']] ? ministerie_identifiers[chartLoop[cl].data[i]['identifier']] : false;
                                                        if (!c) c = ministerie_colors[chartLoop[cl].data[i][category]] ? ministerie_colors[chartLoop[cl].data[i][category]] : '#a90061';
                                                        except_colors.push(c);
                                                    }
                                                    am4core.settings({
                                                        colors: except_colors
                                                    });
                                                } else {
                                                    chart_colors = [];
                                                    for (let c in minfin_colors) {
                                                        chart_colors.push(minfin_colors[c]);
                                                    }
                                                    am4core.settings({
                                                        colors: chart_colors
                                                    });
                                                }

                                                // first fill selects
                                                setTimeout(function () {
                                                    if (minfin_api['available_years']) {
                                                        var dropDownY = document.getElementById('select_year');
                                                        if (dropDownY) {
                                                            var optionsY = '';
                                                            for (let p in minfin_api['available_years']) {
                                                                var checkFase = minfin_api.map[minfin_api.data.phase.toLowerCase()] ? minfin_api.map[minfin_api.data.phase.toLowerCase()].toLowerCase() : minfin_api.data.phase.toLowerCase();
                                                                if (checkFase == p.toLowerCase()) {
                                                                    for (let y in minfin_api['available_years'][p]) {
                                                                        optionsY += '<option value="' + minfin_api['available_years'][p][y] + '">' + minfin_api['available_years'][p][y] + '</option>';
                                                                    }
                                                                }
                                                            }
                                                            dropDownY.innerHTML = optionsY;
                                                            dropDownY.addEventListener('change', gotoVisual);
                                                        }
                                                    }

                                                    var maxyear = setSelect('select_year', minfin_api.data.year);
                                                    if (parseInt(maxyear) && parseInt(maxyear) < parseInt(year)) {
                                                        // we're in the future, go to last available year
                                                        setDrupalPath('year', maxyear);
                                                        //console.log('goto', getDrupalPath() + addQuery(['graph', '#']));
                                                        location.href = getDrupalPath() + addQuery(['graph', '#']);
                                                    }
                                                    setSelect('select_phase', minfin_api.map['nl'][minfin_api.data.phase] ? minfin_api.map['nl'][minfin_api.data.phase] : minfin_api.data.phase);
                                                    setSelect('select_vuo', minfin_api.map['nl'][minfin_api.data.vuo] ? minfin_api.map['nl'][minfin_api.data.vuo] : minfin_api.data.vuo);
                                                    // show
                                                    document.getElementById('navigation_select').style.display = 'block';

                                                }, 100);

                                                // create chart if ready loading all sources
                                                chartLoop[cl].count--;
                                                if (!chartLoop[cl].count) {
                                                    if (chartLoop[cl].data.length) {

                                                        // set legend total
                                                        if (chartLoop[cl].meta['current_year'] && chartLoop[cl].meta['year_totals'] && chartLoop[cl].meta['year_totals'][chartLoop[cl].meta['current_year']]) {
                                                            var total = format_number(chartLoop[cl].meta['year_totals'][chartLoop[cl].meta['current_year']], chartLoop[cl].options);
                                                            chartLoop[cl].meta['legend_total'] = total;
                                                        }

                                                        // set titles
                                                        var target = document.querySelector('.' + minfin_api.anchor);
                                                        if (chartLoop[cl].meta['title']) {
                                                            if (target) target.innerHTML = chartLoop[cl].meta['title'];
                                                            chartLoop[cl].meta['legend_title'] = chartLoop[cl].meta['title'];
                                                        } else if (this.datatype == 'rijksfinancien_single' && this.target) {
                                                            if (target) target.innerHTML = chartLoop[cl].options.title;
                                                            chartLoop[cl].meta['legend_title'] = barTitle;
                                                            chartLoop[cl].options.title = '';
                                                        }

                                                        if (chartLoop[cl].options['legend']) {
                                                            // set link
                                                            if (chartLoop[cl].meta['links']) {
                                                                set_links(chartLoop[cl].meta['links']);
                                                            }

                                                            // set description
                                                            if (chartLoop[cl].meta['description']) {
                                                                set_description(chartLoop[cl].meta['description']);
                                                            }
                                                        }

                                                        //console.log(chartLoop[cl].id, chartLoop[cl].type, chartLoop[cl].data, chartLoop[cl].options, chartLoop[cl].meta)
                                                        //chartLoop[cl].meta.legend_total = '123.456'
                                                        //console.log(JSON.stringify(chartLoop[cl].options));
                                                        _minfin_chart(chartLoop[cl].id, chartLoop[cl].type, chartLoop[cl].data, chartLoop[cl].options, chartLoop[cl].meta);
                                                    } else if (document.getElementById('navigation_links')) {
                                                        // hide link navigation
                                                        document.getElementById('navigation_links').style.display = 'none';
                                                    }
                                                }
                                                break;
                                        }
                                    }
                                }
                            }
                        }
                    });
                    request.addEventListener("error", function (e) {
                        console.warn("Api call failed: " + this.responseText);
                    });
                    request.open("GET", apiPath);
                    request.setRequestHeader("contentType", "application/json; charset=utf-8");
                    request.datatype = datatype;
                    request.target = target;
                    request.send();
                }
            }

            function set_links(links) {
                var target = document.getElementById('links');
                var origin = window.location.origin;

                if (target) {

                    for (let s in links) {

                        if (s) {
                            var subtitle = document.createElement("h3");
                            subtitle.innerHTML = s;
                            target.appendChild(subtitle);
                        }

                        for (let l in links[s]) {
                            if (links[s][l]['description']) {
                                var link = links[s][l]['link'].indexOf('/') == 0 ? origin + links[s][l]['link'] : links[s][l]['link'];
                                var a = document.createElement("div");
                                a.innerHTML = links[s][l]['description'];
                                a.classList.add('double_link');
                                a.setAttribute('url', links[s][l]['link']);
                                a.addEventListener('click', function () {
                                    location.href = this.getAttribute('url');
                                });

                                target.appendChild(a);

                                var c = document.createElement("a");
                                c.innerHTML = link;
                                c.href = links[s][l]['link'];
                                c.classList.add('double_link');
                                c.addEventListener('click', function (e) {
                                    //window.location.href = links[s][l]['link'];
                                    // if (copyToClipboard(e.target)) {
                                    //     alert('De link ' + e.target.innerHTML + ' is naar het klembord gekopieerd.')
                                    // } else {

                                    // }
                                });
                                target.appendChild(c);
                            } else if (links[s][l]['link']) {
                                var link = links[s][l]['link'].indexOf('/') == 0 ? origin + links[s][l]['link'] : links[s][l]['link'];
                                var a = document.createElement("a");
                                a.style.display = 'block';
                                a.innerHTML = link;
                                a.href = links[s][l]['link'];
                                a.classList.add('single_link');

                                target.appendChild(a);
                            }
                        }
                    }
                }
            }

            function copyToClipboard(elem) {
                // create hidden text element, if it doesn't already exist
                var targetId = "_hiddenCopyText_";
                var isInput = elem.tagName === "INPUT" || elem.tagName === "TEXTAREA";
                var origSelectionStart, origSelectionEnd;
                if (isInput) {
                    // can just use the original source element for the selection and copy
                    target = elem;
                    origSelectionStart = elem.selectionStart;
                    origSelectionEnd = elem.selectionEnd;
                } else {
                    // must use a temporary form element for the selection and copy
                    target = document.getElementById(targetId);
                    if (!target) {
                        var target = document.createElement("textarea");
                        target.style.position = "absolute";
                        target.style.left = "-9999px";
                        target.style.top = "0";
                        target.id = targetId;
                        document.body.appendChild(target);
                    }
                    target.textContent = elem.textContent;
                }
                // select the content
                var currentFocus = document.activeElement;
                target.focus();
                target.setSelectionRange(0, target.value.length);

                // copy the selection
                var succeed;
                try {
                    succeed = document.execCommand("copy");
                } catch (e) {
                    succeed = false;
                }
                // restore original focus
                if (currentFocus && typeof currentFocus.focus === "function") {
                    currentFocus.focus();
                }

                if (isInput) {
                    // restore prior selection
                    elem.setSelectionRange(origSelectionStart, origSelectionEnd);
                } else {
                    // clear temporary content
                    target.textContent = "";
                }
                return succeed;
            }

            function set_description(info) {
                var target = document.getElementById('description');
                if (target) {

                    var subtitle = document.createElement("h3");
                    subtitle.innerHTML = "Uitleg";
                    target.appendChild(subtitle);

                    var description = document.createElement("div");
                    description.innerHTML = info;
                    target.appendChild(description);
                }
            }

            // END



            // INIT

            var csv = '';
            var jsn = '';
            var api_trigger = '';
            var initializers = [];
            var initializer = false;

            window.addEventListener('load', function () {

                // see if there is a n initializer template
                var temp = $("#chart_initializer");
                $('script[id="chart_initializer"]').each(function () {
                    if ($(this).attr('name')) {
                        initializers.push($(this).attr('name'));
                    }
                });

                for (var ini in initializers) {
                    initializer = initializers[ini];

                    if (typeof (initializer) == 'undefined' || initializer === 'visuele_begroting' || initializer === 'default' || !initializer) {
                        callApi('rijksfinancien_triple');

                        navigation('chart_navigation', [{
                            id: 'chart_canvas',
                            hide: queryParams['graph'] == 'stacked-column' ? false : true,
                            link: 'Toon trendgrafiek',
                            type: 'pie'
                        }
                            , {
                            id: 'chart_canvas_1',
                            hide: queryParams['graph'] == 'stacked-column' ? true : false,
                            link: 'Toon donut',
                            type: 'stacked-column'
                        }
                        ]);

                        minfin_chart('chart_canvas', 'stacked-column', 'rijksfinancien_triple', {
                            category: 'title',
                            value: 'autoyear',
                            local: false,
                            sort_: 'desc',
                            divider: 1, // deel bedragen door divider voor afronding
                            multiplier: 1000, // extra Bedragen x ... in weergave
                            decimals_: 0,
                            round: 'floor',
                            currency: 'EUR',
                            bartotals: true,
                            legend: true,
                            loader: true,
                            quicklink: false,
                            childlink: true,
                            highlightbarfix: true
                        }); // id, type, data, options{}
                        minfin_chart('chart_canvas_1', 'pie', 'rijksfinancien_triple', {
                            category: 'title',
                            value: 'autoyear',
                            disc: true,
                            sort_: 'desc',
                            size_: 0,
                            divider: 1,
                            multiplier: 1000,
                            decimals_: 0,
                            round: 'floor',
                            currency: 'EUR',
                            legend: false,
                            quicklink: false,
                            childlink: true
                        });

                    } else if (initializer === 'corona_inkomsten') {

                        readAPIFile(handle_json_data, {
                            use_api_title: 'corona_inkomsten-title',
                            anchor: 'corona_inkomsten-title',
                            api: {
                                query: 'year', // string uit querystring, needs documentation
                                default: 2020, // string uit querystring, needs documentation
                                reload: true,
                                structure: ["year", "identifier", "maatregel"],
                                trigger: "uitgavenmaatregelen",
                                type: 'json',
                                url: '/json/corona_visuals'
                            },
                            back_title: "",
                            backlink: true,
                            charset: 'ISO-8859-1',
                            childlink: true,
                            divider: 1,
                            graph: 'pie',
                            legend: true,
                            loader: true,
                            local: true,
                            multiplier: 1000000,
                            negatives: false,
                            quicklink: false,
                            select: [{
                                api: 'uitgavenmaatregelen',
                                type: 'year',
                                options: []
                            }],
                            shades: false,
                            target: 'chart_canvas',
                            use_identifiers_for_colors: false,
                            value: 'amount'
                        });

                    } else if (initializer === 'automatische_stabilisatoren_inkomsten') {

                        readAPIFile(handle_json_data, {
                            anchor: 'automatische_stabilisatoren_inkomsten-title',
                            target: 'chart_canvas',
                            api: {
                                url: '/json/corona_visuals/automatische_stabilisatoren/inkomsten',
                                reload: false,
                                type: 'json',
                                usepath: false
                            },
                            regex: {
                                pattern: "^(Geraamde belasting en premie-inkomsten)",
                                flags: "i",
                                replace: "value",
                                extra: "pattern" // need documentation
                            },
                            nonulvalues: true, // need documentation
                            nohighlight: true,
                            charset: 'ISO-8859-1',
                            quicklink: false,
                            childlink: false,
                            use_identifiers_for_colors: false,
                            legend: true,
                            shades: false,
                            backlink: false,
                            highlightbarfix: true,
                            negatives: false,
                            graph: 'stacked-column',
                            bars: ['Miljoenennota 2020', 'Geraamde belasting en premie-inkomsten financieel jaarverslag rijk (FJR) 2020'],
                            bartotals: true,
                            singlebars: false,
                            bar_size: 0.4,
                            yaxis: true,
                            multiplier: 1000000,
                            divider: 1,
                            colors: ["#007BC7", "#ffffff"]
                        });
                        readAPIFile(handle_json_data, {
                            anchor: 'automatische_stabilisatoren_inkomsten-title2',
                            target: 'chart_canvas_1',
                            api: {
                                url: '/json/corona_visuals/automatische_stabilisatoren/uitgaven',
                                reload: false,
                                type: 'json',
                                usepath: false
                            },
                            regex: {
                                pattern: "^(Gerealiseerde uitgaven WW en bijstand 2020)$",
                                flags: "i",
                                replace: "value"
                            },
                            nohighlight: true,
                            charset: 'ISO-8859-1',
                            quicklink: false,
                            childlink: true,
                            use_identifiers_for_colors: false,
                            legend: true,
                            backlink: false,
                            highlightbarfix: true,
                            shades: false,
                            negatives: false,
                            graph: 'stacked-column',
                            bartotals: true,
                            bar_size: 0.4,
                            bars: ['Miljoenennota 2020', 'Gerealiseerde uitgaven WW en bijstand 2020'],
                            yaxis: true,
                            multiplier: 1000000,
                            divider: 1,
                            colors: ["#007BC7", "#007BC7"],
                            settop: "chart_canvas"
                        });

                    } else if (initializer === 'automatische_stabilisatoren_uitgaven') {

                        readAPIFile(handle_json_data, {
                            anchor: 'automatische_stabilisatoren_uitgaven-title',
                            target: 'chart_canvas',
                            api: {
                                url: '/json/corona_visuals/automatische_stabilisatoren/uitgaven',
                                reload: false,
                                type: 'json',
                                usepath: false
                            },
                            nohighlight: true,
                            charset: 'ISO-8859-1',
                            quicklink: false,
                            childlink: true,
                            use_identifiers_for_colors: false,
                            legend: false,
                            backlink: false,
                            highlightbarfix: true,
                            shades: false,
                            negatives: false,
                            graph: 'stacked-column',
                            bartotals: true,
                            bar_size: 0.4,
                            bars: ['Ontwerpbegroting 2020', 'Geraamde uitgaven WW en bijstand 2020'],
                            yaxis: true,
                            multiplier: 1000000,
                            divider: 1,
                            colors: ["#007BC7", "#007BC7"]
                        });

                    } else if (initializer === 'automatische_stabilisatoren_uitsplitsing') {

                        readAPIFile(handle_json_data, {
                            anchor: 'automatische_stabilisatoren_uitsplitsing-title',
                            target: 'chart_canvas',
                            api: {
                                url: '/json/corona_visuals/automatische_stabilisatoren/uitsplitsing',
                                reload: false,
                                type: 'json',
                                usepath: false
                            },
                            charset: 'ISO-8859-1',
                            quicklink: false,
                            childlink: true,
                            use_identifiers_for_colors: false,
                            legend: true,
                            backlink: false,
                            shades: false,
                            negatives: false,
                            graph: 'stacked-column',
                            bartotals: false,
                            bar_size: 0.8,
                            singlebars: true,
                            yaxis: true,
                            xaxis: false,
                            multiplier: 1000000,
                            divider: 1,
                            round: false,
                            decimals: 0
                        });

                    } else if (initializer === 'uitgavenplafond') {

                        readAPIFile(handle_json_data, {
                            anchor: 'uitgavenplafond-title',
                            target: 'chart_canvas',
                            api: {
                                query: 'year',
                                trigger: "uitgavenplafonds",
                                structure: ['year'],
                                url: '/json/corona_visuals',
                                reload: false,
                                type: 'json'
                            },
                            select: [{
                                api: "uitgavenplafond",
                                type: 'year',
                                options: []
                            }],
                            charset: 'ISO-8859-1',
                            quicklink: false,
                            childlink: true,
                            regex: {
                                pattern: "^(uitgaven|inkomsten)$", // needs documentation
                                flags: "i",
                                replace: "value"
                            },
                            use_identifiers_for_colors: false,
                            legend_: true,
                            legend: [
                                {
                                    title: "Reguliere netto-uitgaven onder het uitgavenplafond",
                                    type: "dash",
                                    color: "black",
                                    key: "reguliere uitgaven"
                                }
                            ],
                            backlink: false,
                            shades: false,
                            negatives: false,
                            graph: 'stacked-column',
                            bars: ['inkomsten', 'uitgaven'],
                            bartotals: true,
                            bar_size: 0.4,
                            yaxis: true,
                            xaxis: true,
                            round: false,
                            decimals: 0,
                            multiplier: 1000000,
                            divider: 1,
                            nohighlight: true,
                            value: 'value',
                            linedata: [
                                {
                                    key: "reguliere uitgaven",
                                    type: "dash",
                                    color: "black",
                                    currency: "EUR"
                                }
                            ]
                        });

                    } else if (initializer === 'uitgavenplafond-vergelijk') {

                        readAPIFile(handle_json_data, {
                            exception: 'uitgavenplafonds2',
                            target: 'chart_canvas_1',
                            api: {
                                url: '/json/corona_visuals/uitgavenplafonds2',
                                reload: false,
                                type: 'json'
                            },
                            //select: ['year'],
                            charset: 'ISO-8859-1',
                            quicklink: false,
                            childlink: true,
                            regex: {
                                pattern: "bedrag_([0-9]{4})",
                                flags: "i",
                                replace: "$1"
                            },
                            legend: false,
                            backlink: false,
                            shades: false,
                            negatives: false,
                            graph: 'stacked-column',
                            bars: [],
                            bartotals: true,
                            bar_size: 0.4,
                            yaxis: true,
                            xaxis: false,
                            multiplier: 1000000,
                            divider: 1,
                            nohighlight: true,
                            barcolors: true, // true, 'reversed' entire bar in 1 color
                            use_identifiers_for_colors: false,
                            selectbox: {
                                max: 5,
                                placeholder: 'Selecteer een hoofdstuk...'
                            },
                            preset: 'identifier'
                        });

                    } else if (initializer === 'tijdlijn') {

                        readAPIFile(handle_json_data, {
                            anchor: 'tijdlijn-title',
                            api: {
                                url: '/json/corona_visuals/tijdlijn_noodpakketten',
                                reload: false,
                                type: 'json'
                            },
                            collect: {
                                from: 10,
                                title: 'Overig'
                            },
                            charset: 'ISO-8859-1',
                            quicklink: false,
                            childlink: false,
                            regex: {
                                pattern: "([0-9]{1,2})-([0-9]{1,2})-([0-9]{2,4})",
                                flags: "i",
                                replace: "$1 $2 $3"
                            },
                            use_identifiers_for_colors: false,
                            legend: true,
                            backlink: false,
                            shades: false,
                            negatives: false,
                            graph: 'stacked-column',
                            bars: ['Voorjaarsnota', 'Miljoenennota', 'Najaarsnota', 'Jaarverslag'],
                            bar: 'Jaarverslag',
                            sort: 'desc',
                            bartotals: true,
                            bar_size: 0.4,
                            yaxis: true,
                            xaxis: true,
                            multiplier: 1000000,
                            divider: 1,
                            nohighlight: true,
                            highlightbarfix: true,
                            truncate: true // needs documentation
                        });

                    } else if (initializer === 'tijdlijn-vergelijk') {

                        //jsn = 'corona-tijdlijn-2.json';
                        //readTextFile(jsn, handle_json_data, {
                        readAPIFile(handle_json_data, {
                            target: 'chart_canvas_1',
                            api: {
                                url: '/json/corona_visuals/tijdlijn_noodpakketten',
                                reload: false,
                                type: 'json'
                            },
                            charset: 'ISO-8859-1',
                            quicklink: false,
                            childlink: true,
                            legend: false,
                            backlink: false,
                            shades: false,
                            negatives: false,
                            graph: 'clustered-column',
                            bars: ['Voorjaarsnota', 'Miljoenennota', 'Najaarsnota', 'Jaarverslag'],
                            bartotals: false,
                            bar_size: 0.4,
                            yaxis: true,
                            xaxis: true,
                            multiplier: 1000000,
                            divider: 1,
                            nohighlight: true,
                            series: [], // array for manual selecting series
                            barcolors: true, // true, 'reversed' entire bar in 1 color
                            use_identifiers_for_colors: false,
                            selectbox: {
                                max: 5,
                                compare: 'series', // needs documentation bars(default)/series
                                placeholder: 'Selecteer een maatregel...'
                            },
                            align: 'left',
                            preset: 'Jaarverslag', // needs documentation
                            onready: 'tijdlijn' // needs documentation
                        });
                    } else if (initializer === 'begroting_vs_realisatie') {

                        readAPIFile(handle_json_data, {
                            use_api_title: 'begroting_vs_realisatie-title',
                            anchor: 'begroting_vs_realisatie-title',
                            target: 'chart_canvas',
                            api: {
                                default: [2020],
                                structure: ["year", "identifier"],
                                trigger: 'begroting_vs_realisatie',
                                url: '/json/corona_visuals',
                                reload: true,
                                type: 'json'
                            },
                            bar: 'Jaarverslag',
                            sort: 'desc',
                            minimise: 'desc',
                            charset: 'ISO-8859-1',
                            quicklink: false,
                            childlink: false,
                            use_identifiers_for_colors: true,
                            legend: true,
                            legend_: [
                                {
                                    title: "Afwijking realisatie t.o.v. ontwerpbegroting",
                                    type: "dash",
                                    color: "black"
                                }
                            ],
                            backlink: false,
                            shades: false,
                            negatives: false,
                            graph: 'stacked-column',
                            bars: ['Ontwerp', '1e supp.', '2e supp.', 'Jaarverslag'],
                            bartotals: true,
                            bar_size: 0.4,
                            yaxis: true,
                            xaxis: true,
                            multiplier: 1000,
                            divider: 1,
                            linedata_: [
                                {
                                    key: "Afwijking realisatie t.o.v. ontwerpbegroting",
                                    type: "dash",
                                    color: "black",
                                    currency: "EUR"
                                }
                            ],
                            highlightbarfix: true
                        });

                    } else if (initializer === 'impact_op_de_staatsschuld') {

                        readAPIFile(handle_json_data, {
                            anchor: 'impact_op_de_staatsschuld-title',
                            target: 'chart_canvas',
                            api: {
                                url: '/json/corona_visuals/emu_schuld',
                                reload: true,
                                type: 'json'
                            },
                            charset: 'ISO-8859-1',
                            quicklink: false,
                            childlink: true,
                            category: "title",
                            value: "value",
                            regex: {
                                pattern: "bedrag_([0-9]{4})",
                                flags: "i",
                                replace: "$1"
                            },
                            use_identifiers_for_colors: false,
                            legend: [
                                {
                                    title: "EMU-schuld",
                                    type: "fill", // fill, pattern, line, bullet, dash
                                    color: "blue"
                                }, {
                                    title: "EMU-schuld in procenten bbp",
                                    type: "bullet",
                                    color: "red"
                                }, {
                                    title: "Europese grenswaarde uit SGP",
                                    type: "dash",
                                    color: "red"
                                }
                            ],
                            axis: {
                                min: 0,
                                max: 100,
                                color: "red"
                            },
                            backlink: false,
                            shades: false,
                            negatives: false,
                            graph: 'stacked-column',
                            bartotals: true,
                            bar_size: 0.6,
                            singlebars: true,
                            yaxis: true,
                            xaxis: true,
                            multiplier: 1000000000,
                            divider: 1,
                            colors: ["blue"],
                            linedata: [
                                {
                                    key: "EMU-schuld in procenten bbp",
                                    type: "bullet",
                                    color: "red",
                                    axis: true
                                }, {
                                    key: "Europese grenswaarde uit SGP",
                                    type: "dash",
                                    color: "red",
                                    axis: true
                                }
                            ]
                        });

                    } else if (initializer === 'emu_saldo') {

                        readAPIFile(handle_json_data, {
                            anchor: 'emu_saldo-title',
                            target: 'chart_canvas',
                            api: {
                                url: '/json/corona_visuals/emu_saldo',
                                reload: true,
                                type: 'json'
                            },
                            value: 'amount',
                            round: false,
                            charset: 'ISO-8859-1',
                            quicklink: false,
                            childlink: true,
                            category: "title",
                            value: "EMU_saldo_(%)_incl correctie 1995",
                            currency: "",
                            regex: {
                                pattern: "bedrag_([0-9]{4})",
                                flags: "i",
                                replace: "$1"
                            },
                            use_identifiers_for_colors: false,
                            legend: [
                                "extraonly",
                                {
                                    title: "EMU-saldo",
                                    type: "fill", // fill, pattern, line, bullet, dash
                                    color: "blue"
                                }, {
                                    title: "Europese grenswaarde uit SGP",
                                    type: "dash",
                                    color: "#000000",
                                    key: "Europese grenswaarde uit SGP"
                                }
                            ],
                            backlink: false,
                            shades: false,
                            negatives: false,
                            graph: 'stacked-column',
                            bartotals: true,
                            bar_size: 0.6,
                            singlebars: true,
                            yaxis: true,
                            xaxis: true,
                            multiplier: 1,
                            divider: 1,
                            colors: ["blue"],
                            linedata: [
                                {
                                    key: "Europese grenswaarde uit SGP",
                                    type: "dash",
                                    color: "black",
                                    single: true
                                }
                            ]
                        });

                    } else if (initializer === 'fiscale_maatregelen') {

                        readAPIFile(handle_json_data, {
                            anchor: 'fiscale_maatregelen-title',
                            charset: 'ISO-8859-1',
                            target: 'chart_canvas',
                            api: {
                                query: 'year',
                                default: [2022],
                                structure: ["year", "identifier", "maatregel"],
                                trigger: "fiscalemaatregelen",
                                url: '/json/corona_visuals',
                                reload: true,
                                type: 'json'
                            },
                            select: [{
                                type: 'year',
                                options: ["2020", "2021", "2022", "2023", "2024", "2025"]
                            }],
                            category: 'title',
                            value: 'amount',
                            quicklink: false,
                            childlink: true,
                            use_identifiers_for_colors: false,
                            legend: true,
                            backlink: false,
                            shades: false,
                            negatives: false,
                            graph: 'stacked-column',
                            bartotals: true,
                            bar_size: 0.6,
                            singlebars: true,
                            yaxis: true,
                            xaxis: false,
                            multiplier: 1000000,
                            divider: 1,
                        });

                    } else if (initializer === 'garanties') {

                        readAPIFile(handle_json_data, {
                            anchor: 'garanties-titled',
                            charset: 'ISO-8859-1',
                            target: 'chart_canvas',
                            api: {
                                query: 'year',
                                default: [2022],
                                structure: ["year", "identifier", "maatregel"],
                                url: '/json/corona_visuals',
                                reload: true,
                                trigger: 'garanties',
                                type: 'json'
                            },
                            select: [{
                                api: 'garanties',
                                type: 'year',
                                options: []
                            }],
                            value: 'amount',
                            quicklink: false,
                            childlink: true,
                            use_identifiers_for_colors: true,
                            legend: true,
                            shades: false,
                            backlink: true,
                            negatives: false,
                            graph: 'pie',
                            multiplier: 1000000,
                            divider: 1,
                            local: true,
                            loader: true
                        });

                    } else if (initializer === 'uitstel_van_betaling') {

                        readAPIFile(handle_json_data, {
                            anchor: 'uitstel_van_betaling-title',
                            charset: 'ISO-8859-1',
                            target: 'chart_canvas',
                            api: {
                                default: [2020],
                                structure: ["year", "identifier", "maatregel"],
                                trigger: "belastinguitstel",
                                url: '/json/corona_visuals',
                                reload: true,
                                type: 'json'
                            },
                            category: 'title',
                            value: 'amount',
                            quicklink: false,
                            childlink: true,
                            use_identifiers_for_colors: false,
                            legend: true,
                            backlink: false,
                            shades: false,
                            negatives: false,
                            graph: 'stacked-column',
                            bartotals: true,
                            bar_size: 0.6,
                            singlebars: true,
                            yaxis: true,
                            xaxis: false,
                            multiplier: 1000000,
                            divider: 1,
                        });

                    } else if (initializer === 'leningen') {

                        readAPIFile(handle_json_data, {
                            use_api_title: 'leningen-title',
                            anchor: 'leningen-title',
                            charset: 'ISO-8859-1',
                            target: 'chart_canvas',
                            select: [{
                                api: 'leningen',
                                type: 'year',
                                options: []
                            }],
                            api: {
                                query: 'year',
                                default: 2022,
                                structure: ["year", "identifier", "maatregel"],
                                trigger: "leningen",
                                url: '/json/corona_visuals',
                                reload: true,
                                type: 'json'
                            },
                            value: 'amount',
                            quicklink: false,
                            childlink: true,
                            use_identifiers_for_colors: true,
                            legend: true,
                            shades: false,
                            backlink: true,
                            negatives: false,
                            graph: 'pie',
                            multiplier: 1000000,
                            divider: 1,
                            local: true,
                            loader: true,
                            colors: 'reversed'
                        });



                        /*
                            from here only first csv versions, probably all unused by now
                        */



                    } else if (initializer === 'budgettaire_gevolgen') {
                        // version 1, csv variant
                        am4core.settings({
                            anchor: 'budgettaire_gevolgen-title',
                            delay_: 350,
                            inactive_color_: '#e6e6e6',
                            stroke_color_: '#ffffff',
                            stroke_width_: 0.5,
                            dimmed_opacity_: 0.7,
                            font_: 'Times New Roman' // unused
                        });

                        csv = 'budgettaire-gevolgen';
                        api_trigger = 'budgettaire-gevolgen';

                        minfin_api['available_years'] = {}
                        minfin_api.available_years[api_trigger] = ["2020"];
                        minfin_api.drupal_structure = [api_trigger, "year", "vuo", "identifier"];
                        //minfin_api.anchor = 'income-title';

                        getDrupalParts();

                        if (!minfin_api.index) {
                            minfin_api.index = minfin_api['parts'].length;
                            if (minfin_api['parts'][minfin_api['parts'].length - 1].indexOf('?')) {
                                minfin_api['parts'][minfin_api['parts'].length - 1] += '&pad=';
                            } else {
                                minfin_api['parts'][minfin_api['parts'].length - 1] += '?pad=';
                            }
                        }
                        if (!minfin_api.path[api_trigger]) {
                            minfin_api['parts'].push(api_trigger);
                            minfin_api.path[api_trigger] = api_trigger;
                        }
                        if (!minfin_api.path['year']) {
                            minfin_api['parts'].push(minfin_api.path['year']);
                            minfin_api.path['year'] = '2020';
                        }
                        if (!minfin_api.path['vuo']) {
                            minfin_api['parts'].push(minfin_api.path['vuo']);
                            minfin_api.path['vuo'] = 'U';
                        }
                        if (!minfin_api.path['identifier']) {
                            minfin_api['parts'].push(minfin_api.path['identifier']);
                            minfin_api.path['identifier'] = '0';
                        }
                        readAPIFile(handle_csv_data, {
                            api: {
                                url: '/csv/corona_visuals/' + csv,
                                type: 'csv'
                            },
                            charset: 'ISO-8859-1',
                            quicklink: false,
                            childlink: true,
                            use_identifiers_for_colors: false,
                            shades: false,
                            negatives: false,
                            graph: 'stacked-column',
                            bartotals: true,
                            bar_size: 0.3,
                            bars: ['2020'],
                            yaxis: false,
                            multiplier: 1000000,
                            divider: 1000000000000
                        });
                    } else if (initializer === 'corona') {
                        // version 1, csv variant
                        csv = 'noodmaatregelen_coronacrisis';
                        //csv = 'corona-inkomsten.csv';
                        api_trigger = 'corona';

                        minfin_api['available_years'] = {}
                        minfin_api.available_years[api_trigger] = ["2020"];
                        minfin_api.drupal_structure = [api_trigger, "year", "vuo", "identifier"];
                        minfin_api.anchor = 'corona-title';

                        getDrupalParts();

                        if (!minfin_api.index) {
                            minfin_api.index = minfin_api['parts'].length;
                            if (minfin_api['parts'][minfin_api['parts'].length - 1].indexOf('?')) {
                                minfin_api['parts'][minfin_api['parts'].length - 1] += '&pad=';
                            } else {
                                minfin_api['parts'][minfin_api['parts'].length - 1] += '?pad=';
                            }
                        }
                        if (!minfin_api.path[api_trigger]) {
                            minfin_api['parts'].push(api_trigger);
                            minfin_api.path[api_trigger] = api_trigger;
                        }
                        if (!minfin_api.path['year']) {
                            minfin_api['parts'].push(minfin_api.path['year']);
                            minfin_api.path['year'] = '2020';
                        }
                        if (!minfin_api.path['vuo']) {
                            minfin_api['parts'].push(minfin_api.path['vuo']);
                            minfin_api.path['vuo'] = 'U';
                        }
                        if (!minfin_api.path['identifier']) {
                            minfin_api['parts'].push(minfin_api.path['identifier']);
                            minfin_api.path['identifier'] = '0';
                        }
                        // readTextFile (local)
                        readAPIFile('/csv/corona_visuals/' + csv, handle_csv_data, {
                            charset: 'ISO-8859-1',
                            quicklink: true,
                            childlink: true,
                            use_identifiers_for_colors: true,
                            shades: false,
                            negatives: false,
                            multiplier: 1000000,
                            divider: 1,
                            graph: 'pie'
                        });
                        document.getElementById('navigation_select').style.textAlign = 'center';
                    } else if (initializer === 'uitgaven_maatregelen') {
                        // version 1, csv variant
                        am4core.settings({
                            anchor: 'uitgaven-maatregelen-title'
                        });

                        csv = 'uitgavenmaatregelen';
                        api_trigger = 'uitgaven-maatregelen';

                        minfin_api['available_years'] = {}
                        minfin_api.available_years[api_trigger] = [""];
                        minfin_api.drupal_structure = [api_trigger, "year", "vuo", "identifier"];

                        getDrupalParts();

                        if (!minfin_api.index) {
                            minfin_api.index = minfin_api['parts'].length;
                            if (minfin_api['parts'][minfin_api['parts'].length - 1].indexOf('?')) {
                                minfin_api['parts'][minfin_api['parts'].length - 1] += '&pad=';
                            } else {
                                minfin_api['parts'][minfin_api['parts'].length - 1] += '?pad=';
                            }
                        }
                        if (!minfin_api.path[api_trigger]) {
                            minfin_api['parts'].push(api_trigger);
                            minfin_api.path[api_trigger] = api_trigger;
                        }
                        if (!minfin_api.path['year']) {
                            minfin_api['parts'].push(minfin_api.path['year']);
                            minfin_api.path['year'] = 'Aangepaste ontwerpbegroting 2020';
                        }
                        if (!minfin_api.path['vuo']) {
                            minfin_api['parts'].push(minfin_api.path['vuo']);
                            minfin_api.path['vuo'] = 'U';
                        }
                        if (!minfin_api.path['identifier']) {
                            minfin_api['parts'].push(minfin_api.path['identifier']);
                            minfin_api.path['identifier'] = '0';
                        }
                        readAPIFile(handle_csv_data, {
                            api: {
                                url: '/csv/corona_visuals/' + csv,
                                type: 'csv'
                            },
                            charset: 'ISO-8859-1',
                            quicklink: false,
                            childlink: true,
                            use_identifiers_for_colors: false,
                            shades: false,
                            negatives: false,
                            graph: 'stacked-column',
                            bartotals: true,
                            bar_size: 0.4,
                            yaxis: false,
                            bars: ["Ontwerpbegroting 2020", "Aangepaste ontwerpbegroting 2020"],
                            multiplier: 1000000,
                            divider: 1000000000000,
                            colors: ["#d52b1e", "#d52b1e", "#94710B"]
                        });

                    } else if (initializer === 'automatische_stabilisatoren') {
                        // version 1, csv variant
                        am4core.settings({
                            anchor: 'automatische-stabilisatoren-title'
                        });

                        csv = 'corona-automatische-stabilisatoren.csv';
                        api_trigger = 'automatische-stabilisatoren';

                        minfin_api['available_years'] = {}
                        minfin_api.available_years[api_trigger] = ["2020"];
                        minfin_api.drupal_structure = [api_trigger, "year", "vuo", "identifier"];
                        //minfin_api.anchor = 'income-title';

                        getDrupalParts();

                        if (!minfin_api.index) {
                            minfin_api.index = minfin_api['parts'].length;
                            if (minfin_api['parts'][minfin_api['parts'].length - 1].indexOf('?')) {
                                minfin_api['parts'][minfin_api['parts'].length - 1] += '&pad=';
                            } else {
                                minfin_api['parts'][minfin_api['parts'].length - 1] += '?pad=';
                            }
                        }
                        if (!minfin_api.path[api_trigger]) {
                            minfin_api['parts'].push(api_trigger);
                            minfin_api.path[api_trigger] = api_trigger;
                        }
                        if (!minfin_api.path['year']) {
                            minfin_api['parts'].push(minfin_api.path['year']);
                            minfin_api.path['year'] = '2020';
                        }
                        if (!minfin_api.path['vuo']) {
                            minfin_api['parts'].push(minfin_api.path['vuo']);
                            minfin_api.path['vuo'] = 'U';
                        }
                        if (!minfin_api.path['identifier']) {
                            minfin_api['parts'].push(minfin_api.path['identifier']);
                            minfin_api.path['identifier'] = '0';
                        }
                        readTextFile(csv, handle_csv_data, {
                            charset: 'ISO-8859-1',
                            quicklink: false,
                            childlink: true,
                            use_identifiers_for_colors: false,
                            shades: false,
                            multiplier: 1000000,
                            divider: 1000000000000,
                            negatives: false,
                            graph: 'pie'
                        });

                    } else if (initializer === 'uitstel_van_betaling_oud') {
                        // version 1, csv variant
                        am4core.settings({
                            anchor: 'uitstel_van_betaling-title',
                        });

                        api_trigger = 'belastinguitstel';
                        minfin_api['available_years'] = {}
                        minfin_api.available_years[api_trigger] = ["2020"];
                        minfin_api.drupal_structure = [api_trigger, "year", "identifier", "maatregel"];

                        if (typeof (minfin_api.path[api_trigger]) == 'undefined') {
                            minfin_api.path[api_trigger] = api_trigger;
                        }
                        if (typeof (minfin_api.path['year']) == 'undefined') {
                            minfin_api.path['year'] = 2020;
                        }
                        getDrupalParts();

                        jsn = 'corona-uitstel-van-betaling.json';
                        //readTextFile(jsn, handle_json_data, {
                        readAPIFile(handle_json_data, {
                            exception: 'uitstel_van_betaling',
                            target: 'chart_canvas',
                            api: {
                                url: '/json/corona_visuals',
                                reload: true,
                                type: 'json'
                            },
                            select: ['year'],
                            charset: 'ISO-8859-1',
                            quicklink: false,
                            childlink: true,
                            value: 'identifier',
                            category_: 'title',
                            use_identifiers_for_colors: false,
                            legend: true,
                            shades: false,
                            backlink: true,
                            negatives: false,
                            singlebars: false,
                            graph: 'stacked-column',
                            barcolors: true, // true, 'reversed' entire bar in 1 color
                            nohighlight: true,
                            bars: ['Accijnzen', 'Assurantiebelasting', 'Belasting op personenauto\u0027s en motorrijwielen', 'Belastingen op een milieugrondslag', 'Kansspelbelasting', 'Loon- en inkomensheffing', 'Vennootschapsbelasting'],
                            bartotals: true,
                            bar_size: 0.6,
                            multiplier: 1000000,
                            divider: 1,
                            yaxis: true,
                            xaxis: false
                        });
                    } else if (initializer === 'belastinguitstel') {
                        // version 1, csv variant
                        am4core.settings({
                            anchor: 'belastinguitstel-title'
                        });

                        csv = 'belastinguitstel';
                        api_trigger = 'belastinguitstel';

                        minfin_api['available_years'] = {}
                        minfin_api.available_years[api_trigger] = ["2020"];
                        minfin_api.drupal_structure = [api_trigger, "year", "vuo", "identifier"];
                        //minfin_api.anchor = 'income-title';

                        getDrupalParts();

                        if (!minfin_api.index) {
                            minfin_api.index = minfin_api['parts'].length;
                            if (minfin_api['parts'][minfin_api['parts'].length - 1].indexOf('?')) {
                                minfin_api['parts'][minfin_api['parts'].length - 1] += '&pad=';
                            } else {
                                minfin_api['parts'][minfin_api['parts'].length - 1] += '?pad=';
                            }
                        }
                        if (!minfin_api.path[api_trigger]) {
                            minfin_api['parts'].push(api_trigger);
                            minfin_api.path[api_trigger] = api_trigger;
                        }
                        if (!minfin_api.path['year']) {
                            minfin_api['parts'].push(minfin_api.path['year']);
                            minfin_api.path['year'] = '2020';
                        }
                        if (!minfin_api.path['vuo']) {
                            minfin_api['parts'].push(minfin_api.path['vuo']);
                            minfin_api.path['vuo'] = 'U';
                        }
                        if (!minfin_api.path['identifier']) {
                            minfin_api['parts'].push(minfin_api.path['identifier']);
                            minfin_api.path['identifier'] = '0';
                        }
                        readAPIFile(handle_csv_data, {
                            api: {
                                url: '/csv/corona_visuals/' + csv,
                                type: 'csv'
                            },
                            response: 'csv',
                            charset: 'ISO-8859-1',
                            quicklink: false,
                            childlink: true,
                            use_identifiers_for_colors: false,
                            shades: false,
                            negatives: false,
                            graph: 'pie',
                            colors: 'reversed'
                        });
                    } else if (initializer === 'begrotingsgedekte-maatregelen') {
                        // version 1, csv variant
                        am4core.settings({
                            anchor: 'begrotingsgedekte-maatregelen-title'
                        });

                        csv = 'begrotingsgedekte_maatregelen';
                        api_trigger = 'begrotingsgedekte-maatregelen';

                        minfin_api['available_years'] = {}
                        minfin_api.available_years[api_trigger] = ["2020"];
                        minfin_api.drupal_structure = [api_trigger, "year", "vuo", "identifier"];
                        //minfin_api.anchor = 'income-title';

                        getDrupalParts();

                        if (!minfin_api.index) {
                            minfin_api.index = minfin_api['parts'].length;
                            if (minfin_api['parts'][minfin_api['parts'].length - 1].indexOf('?')) {
                                minfin_api['parts'][minfin_api['parts'].length - 1] += '&pad=';
                            } else {
                                minfin_api['parts'][minfin_api['parts'].length - 1] += '?pad=';
                            }
                        }
                        if (!minfin_api.path[api_trigger]) {
                            minfin_api['parts'].push(api_trigger);
                            minfin_api.path[api_trigger] = api_trigger;
                        }
                        if (!minfin_api.path['year']) {
                            minfin_api['parts'].push(minfin_api.path['year']);
                            minfin_api.path['year'] = '2020';
                        }
                        if (!minfin_api.path['vuo']) {
                            minfin_api['parts'].push(minfin_api.path['vuo']);
                            minfin_api.path['vuo'] = 'U';
                        }
                        if (!minfin_api.path['identifier']) {
                            minfin_api['parts'].push(minfin_api.path['identifier']);
                            minfin_api.path['identifier'] = '0';
                        }
                        readAPIFile(handle_csv_data, {
                            api: {
                                url: '/csv/corona_visuals/' + csv,
                                type: 'csv'
                            },
                            response: 'csv',
                            charset: 'ISO-8859-1',
                            quicklink: false,
                            childlink: true,
                            use_identifiers_for_colors: false,
                            shades: false,
                            negatives: false,
                            graph: 'pie'
                        });
                    } else {
                        // hook
                        executeFunctionByName(initializer, window);
                    }
                }

                // initialize key event
                document.addEventListener('keydown', handleKeyDown);
                document.addEventListener('keyup', handleKeyUp);
            });

            /*
            // execute function from template
            */
            function executeFunctionByName(functionName, localContext) {

                var args = Array.prototype.slice.call(arguments, 2);

                if (args.length) {
                    if (args.length == 1 && (args[0].length || Object.keys(args[0]).length)) {
                        args = args[0];
                    }
                }

                if (functionName == 'verzelfstandigingen') {
                    return verzelfstandigingen();
                } else if (functionName == 'handling_verzelfstandigingen') {
                    return handling_verzelfstandigingen(args[0], args[1]);
                } else if (functionName == 'handling_verzelfstandigingen_vergelijk') {
                    return handling_verzelfstandigingen_vergelijk(args[0], args[1]);
                } else if (functionName == 'change_vzst_vergelijk') {
                    return change_vzst_vergelijk(args[0], args[1], args[2]);
                } else if (functionName == 'change_slider_vrzs') {
                    return change_slider_vrzs(args);
                } else if (functionName == 'set_manual_bars') {
                    return set_manual_bars(args[0], args[1], args[2]);
                } else if (functionName == 'handling_wie_ontv') {
                  return handling_wie_ontv(args[0], args[1]);
                } else if (functionName == 'handling_wie_ontv_start') {
                    return handling_wie_ontv_start(args[0], args[1]);
                } else if (functionName == 'change_slider_wie_ontv') {
                    return change_slider_wie_ontv(args);
                } else if (functionName == 'wie_ontvingen') {
                  return wie_ontvingen(args);
                } else if (functionName == 'wie_ontvingen_start') {
                  return wie_ontvingen_start(args);
                } else if (functionName == 'set_min_wie_ontv') {
                    return set_min_wie_ontv(args);
                } else if (functionName == 'reload_wie_ontv') {
                    return reload_wie_ontv(args);
                } else if (functionName == 'wie_ontv_year') {
                    return wie_ontv_year(args);
                } else { // does not work within drupal :-(
                    var namespaces = functionName.split(".");
                    var func = namespaces.pop();
                    for (var i = 0; i < namespaces.length; i++) {
                        localContext = localContext[namespaces[i]];
                    }
                    if (localContext[func]) {
                        return localContext[func].apply(localContext, args);
                    } else {
                        console.warn('Unknown function call to ' + func);
                    }
                }
            }

            // handle keydown
            function handleKeyDown(e) {
              curKeyDown = e.keyCode;
              if (e.keyCode === 13) {
                let isPageAction = hasSomeParentTheClass(document.activeElement, 'page-actions');
                if(!isPageAction) {
                  document.activeElement.click();
                }
              }
            }

            // returns true if the element or one of its parents has the class classname
            function hasSomeParentTheClass(element, classname) {
              if(element.className) {
                if (element.className.split(' ').indexOf(classname)>=0) return true;
              }
              return element.parentNode && hasSomeParentTheClass(element.parentNode, classname);
            }

            // handle keyup
            function handleKeyUp(e) {
                sluit_popup(-2, false);
                curKeyDown = false;
            }

            var csv_structure = ['Titel', 'Hoofdstuk', 'Maatregel', 'Specificatie', 'nochild'];
            var csv_values = [];
            var csv_data = [];
            var csv_date = '';
            var csv_bedrag = false;
            var csv_toelichting_bedrag = false;
            var csv_totals = [0, 0, 0, 0];
            function handle_json_data(tmp_data, options) {

                // set last update
                csv_date = typeof (tmp_data['date']) != 'undefined' ? tmp_data['date'] : "";

                // complement option settings
                options['value'] = typeof (options['bar']) != 'undefined' ? options['bar'] : options['value'];
                options['back_title'] = typeof (options['back_title']) != 'undefined' ? options['back_title'] : '';
                options['previous'] = typeof (options['previous']) != 'undefined' ? options['previous'] : false;
                options['value'] = typeof (options['value']) != 'undefined' ? options['value'] : typeof (minfin_api.path['year']) != 'undefined' ? minfin_api.path['year'] : 'value';
                options['category'] = typeof (options['category']) != 'undefined' ? options['category'] : 'title';
                var links = typeof (tmp_data['link']) != 'undefined' && tmp_data['link'] ? tmp_data['link'].split('|') : [];
                if (links.length && links[0] != '') {
                    for (var l in links) {

                        var info = links[l].trim();
                        var matches = info.match(/^\[([^\[\]]+)\]/, 'gi');
                        if (matches) {
                            links[l] = links[l].replace(matches[0], '');
                        }

                        links[l] = { link: links[l].trim() }
                        if (matches) {
                            links[l].description = matches[1];
                        }
                    }
                    links = { 'Publicaties': links };
                } else {
                    links = '';
                }
                options['title'] = typeof (tmp_data['title']) != 'undefined' ? tmp_data['title'] : options['title'] ? options['title'] : '';
                options['page_title'] = typeof (tmp_data['page_title']) != 'undefined' ? tmp_data['page_title'] : options['page_title'] ? options['page_title'] : '';
                options['legend_title'] = typeof (tmp_data['legend_title']) != 'undefined' ? tmp_data['legend_title'] : options['title'];
                var multiplier_text = options.multiplier ? options.multiplier : options.divider ? options.divider : 1000000;
                multiplier_text = multiplier_text == 1000000 ? "Alle bedragen in miljoenen" : multiplier_text > 1 ? "Alle bedragen x" + multiplier_text : '';

                /*
                var multiplier_text = parseInt(options.multiplier) * parseInt(options.divider);
                            label.text = multiplier_text == 1000000000 ? "Alle bedragen in miljarden" : multiplier_text == 1000000 ? "Alle bedragen in miljoenen" : multiplier_text > 1 ? "Alle bedragen x" + multiplier_text : '';
                */
                options['links'] = links;
                options['description'] = typeof (tmp_data['description']) != 'undefined' && tmp_data['description'] ? tmp_data['description'] : '';
                options['currency'] = typeof (options['currency']) != 'undefined' ? options['currency'] : 'EUR';
                options['decimals'] = typeof (options['decimals']) != 'undefined' ? options['decimals'] : 0;
                options['disc'] = typeof (options['disc']) != 'undefined' ? options['disc'] : true; // add center disc to donut
                options['local'] = typeof (options['local']) != 'undefined' ? options['local'] : true; // no interaction with other charts
                options['round'] = typeof (options['round']) != 'undefined' ? options['round'] : 'ceil'; // ?
                options['charset'] = typeof (options['charset']) != 'undefined' ? options['charset'] : 'UTF-8';

                var data = [[], options];
                var fillbars = !options.bars || !options.bars.length ? true : false;
                // collect actual data from json
                if (typeof (tmp_data['children']) == 'object' && tmp_data['children'].length) {

                    // ORDER
                    if (typeof (options['sort']) != 'undefined') {
                        var bar = typeof (options['bar']) != 'undefined' ? options['bar'] : typeof (options['bars']) != 'undefined' ? options['bars'][0] : false;
                        if (bar) {
                            sortVal[1] = options['sort'].toLowerCase();
                            sortVal[0] = bar;
                            var c = tmp_data['children'];
                            c.sort(sortArray);
                            tmp_data['children'] = c;
                        }
                    }

                    if (typeof (options['collect']) != 'undefined') {
                        var c = tmp_data['children'];
                        var other = {
                            title: options['collect']['title']
                        };
                        var hasother = false;
                        var bar = typeof (options['bar']) != 'undefined' ? options['bar'] : typeof (options['bars']) != 'undefined' ? options['bars'][0] : false;
                        tmp_data['children'] = [];
                        for (var cc in c) {
                            if (cc < parseInt(options['collect']['from'])) {
                                tmp_data['children'].push(c[cc]);
                            } else {
                                if (options['collect']['title']) {
                                    hasother = true;
                                }
                                for (var b in options.bars) {
                                    if (!other[options.bars[b]]) {
                                        other[options.bars[b]] = 0;
                                    }
                                    other[options.bars[b]] += c[cc][options.bars[b]];
                                }
                            }
                        }
                        if (hasother) {
                            tmp_data['children'].push(other);
                        }
                    }

                    for (var y in tmp_data['children']) {
                        if (typeof (tmp_data['children'][y]['children']) == 'object' && tmp_data['children'][y]['children'].length) {
                            tmp_data['children'][y]['child_info'] = true;
                            if (typeof (tmp_data['children'][y]['identifier']) != 'undefined') {
                                var apitype = typeof (options['api']) != 'undefined' && typeof (options['api']['type']) == 'string' ? options['api']['type'] : 'api';
                                tmp_data['children'][y]['link'] = apitype + ':' + tmp_data['children'][y]['identifier'];
                            }
                        }

                        //tmp_data['children'][y]['description'] = 'mek';

                        if (typeof (tmp_data['children'][y]['identifier']) != 'undefined' && typeof (tmp_data['children'][y]['description']) != 'undefined' && (typeof (tmp_data['children'][y]['link']) != 'undefined' || tmp_data['children'][y]['link'] == '' || !tmp_data['children'][y]['link'])) {
                            var apitype = typeof (options['api']) != 'undefined' && typeof (options['api']['type']) == 'string' ? options['api']['type'] : 'api';
                            tmp_data['children'][y]['link'] = apitype + ':' + tmp_data['children'][y]['identifier'];
                        }

                        if (options.singlebars) {
                            if (options.regex && typeof (options.regex.pattern) == 'string' && typeof (options.regex.replace) == 'string') {
                                var re = new RegExp(options.regex.pattern, typeof (options.regex.flags) == 'string' ? options.regex.flags : '');
                                    tmp_data['children'][y][options.category] = tmp_data['children'][y][options.category].replace(re, options.regex.replace);
                            }
                            tmp_data['children'][y][tmp_data['children'][y][options.category]] = tmp_data['children'][y][options.value];
                        } else if (fillbars) {
                            // seek all regex keys to fill bars
                        } else {
                            if (options.regex && typeof (options.regex.pattern) == 'string' && typeof (options.regex.replace) == 'string') {
                                for (var k in tmp_data['children'][y]) {
                                    var re = new RegExp(options.regex.pattern, typeof (options.regex.flags) == 'string' ? options.regex.flags : '');
                                    var found = k.match(re);
                                    if (typeof (options.regex.extra) == 'string' && tmp_data['children'][y]['extra'].indexOf(options.regex.extra) == -1) {
                                        found = false;
                                    }
                                    if (found) {
                                        tmp_data['children'][y][options.regex.replace] = tmp_data['children'][y][k]
                                    }
                                }
                            }
                        }
                        data[0].push(tmp_data['children'][y]);
                    }
                    // collect bars
                    if (options.singlebars && options.category) {
                        options.bars = [];
                        for (var y in tmp_data['children']) {
                            options.bars.push(tmp_data['children'][y][options.category])
                        }
                    }
                    // add all bars with zeros
                    for (var y in tmp_data['children']) {
                        for (var b in options.bars) {
                            if (typeof (tmp_data['children'][y][options.bars[b]]) == 'undefined') {
                                tmp_data['children'][y][options.bars[b]] = 0;
                                //console.log(1, options.bars[b])
                                delete tmp_data['children'][y][options.bars[b]];
                            }
                        }
                    }
                } else {
                    var colorindex = -1;
                    // hook
                    try {
                        data[0] = executeFunctionByName(options.api.handling, window, tmp_data, options);
                        if (options && options.collect && options.collect.from && data[0].length) {
                            if (data[0][data[0].length - 1]['title'] && data[0][data[0].length - 1]['title'] == options.collect.title) {
                                colorindex = data[0].length - 2;
                            } else {
                                colorindex = data[0].length - 1;
                            }
                        }
                    } catch (error) {
                        console.log('Missing function ' + options.api.handling);
                    }
                }

                // reorder data to match bars for coloring puposes
                if (options.bars && options.bars.length != 0 && options.barcolors) {
                    var tmpdata = [];
                    // add bar data in order
                    for (var b in options.bars) {
                        for (var d in data[0]) {
                            if (data[0][d]['identifier'] == options.bars[b]) {
                                tmpdata.push(data[0][d]);
                            }
                        }
                    }
                    // add all others
                    for (var d in data[0]) {
                        if (options.bars.indexOf(data[0][d]['identifier']) == -1) {
                            tmpdata.push(data[0][d]);
                        }
                    }
                    // put everything back
                    data[0] = [];
                    for (var d in tmpdata) {
                        data[0].push(tmpdata[d]);
                    }
                }

                // set default colors
                var colorset = [];
                for (var c = 0; c < minfin_colors.length; c++) {
                    var col = data[0][c] && data[0][c].nodeColor ? data[0][c].nodeColor : minfin_colors[c];
                    colorset.push(col);
                }

                // set colors if available in options
                if (typeof (options.colors) != 'undefined') {
                    if (options.colors == 'reversed') {
                        colorset = [];
                        for (var c = minfin_colors.length - 1; c >= 0; c--) {
                            colorset.push(minfin_colors[c]);
                        }
                    } else {
                        var colorset = [];
                        for (var c in options.colors) {
                            colorset.push(get_minfin_color(options.colors[c]));
                        }
                    }
                } else if (typeof (options.barcolors) != 'undefined' && typeof (options.bars) == 'object' && options.bars.length) {
                    //TODO add specific colors for each bar from barcolors as list
                    if (options.barcolors == 'reversed') {
                        //minfin_colors.length - 1;
                    }
                    var colorset = [];
                    var colorcounter = -1;
                    var colorcounterkey = '';
                    var barcolors = [];
                    if (!options['colors_']) {
                        options['colors_'] = [];
                    }
                    if (typeof (data[0]) == 'object' && data[0].length) {
                        var tb = false;
                        for (var y in data[0]) {
                            for (var b in options.bars) {
                                tb = true;
                                if (typeof (data[0][y][options.bars[b]]) != 'undefined') {
                                    if (colorcounterkey != options.bars[b]) {
                                        colorcounterkey = options.bars[b];
                                        colorcounter++;
                                    }
                                    // TODO cannot use identifiers because there are a lot of colors without patterns yet
                                    // var id = tmp_data['children'][y]['identifier'] ? tmp_data['children'][y]['identifier'] : -1;
                                    // var ttl = tmp_data['children'][y]['title'] ? tmp_data['children'][y]['title'] : -1;
                                    // var col = ttl != -1 && ministerie_colors[ttl] ? ministerie_colors[ttl] : id != -1 && ministerie_identifiers[id] ? ministerie_identifiers[id] : -1;
                                    col = -1;
                                    if (col == -1) {
                                        col = minfin_colors[colorcounter];
                                        if (options.barcolors == 'reversed') {
                                            col = minfin_colors[minfin_colors.length - 1 - colorcounter];
                                        }
                                    }
                                    colorset.push(get_minfin_color(col));
                                    barcolors[colorcounter] = col;
                                }
                            }
                        }
                    }
                }
                // add 'other' color
                // query
                if (colorindex > -1) {
                    var tc = colorset[0] == '#01689B' ? 1 : 0;
                    while (colorset.length < colorindex + 1) {
                        colorset.push(colorset[tc]);
                        tc++;
                    }
                    colorset[colorindex + 1] = '#B6B6B6';
                }

                // save barcolors
                options['colors_'] = barcolors;

                // add curyear bool
                for (var d in data[0]) {
                    if (options['value'] == minfin_api.path['year']) {
                        if (typeof (minfin_api.path['year']) == 'undefined' || data[0][d][minfin_api.path['year']]) {
                            data[0][d]['curyear'] = true;
                        }
                    } else {
                        data[0][d]['curyear'] = true;
                    }
                }

                // multiplier stuff
                var multiplier = 1; //options.multiplier ? options.multiplier : 1000000;
                for (var d in data[0]) {
                    for (var b in data[1]['bars']) {
                        var v = parseFloat(data[0][d][data[1]['bars'][b]]) * multiplier;
                        if (!isNaN(v)) {
                            data[0][d][data[1]['bars'][b]] = parseFloat(data[0][d][data[1]['bars'][b]]) * multiplier;
                        }
                    }
                }

                // set links and description
                if (options.links != '') {
                    set_links(options.links);
                }
                if (options.description != '') {
                    set_description(options.description);
                }

                // first fill selects and inputs
                var trigger = typeof (options.api) != 'undefined' && typeof (options.api.trigger) != 'undefined' ? options.api.trigger : api_trigger;
                if (1 == 1 || trigger != '') {
                    var available_years = typeof (options.api) != 'undefined' && typeof (options.api.available_years) != 'undefined' && typeof (options.api.available_years) != 'undefined' ? options.api.available_years : typeof (minfin_api['available_years']) != 'undefined' && typeof (minfin_api['available_years'][trigger]) != 'undefined' && minfin_api['available_years'][trigger].length > 1 ? minfin_api['available_years'][trigger] : [];

                    //TODO: make navigation chart dependent
                    //TODO: minfin_api must be independent too
                    // inputs
                    if (options.input && options.input.length) {
                        for (var oi in options.input) {
                            if (options.input[oi] && options.input[oi].type && options.input[oi].query) {
                                var t = options.input[oi].type;
                                var q = options.input[oi].query;
                                var v = tmp_data[q] ? tmp_data[q] : options.api['query'][q];
                                var obj = $('#' + options.target + '_navigation #input_' + t);
                                if (!obj.length) obj = $('#' + options.target + '_navigation_top #input_' + t);
                                if (obj) {
                                    obj.attr('query', q);
                                    if(options.input[oi].placeholder) {
                                        obj.attr('placeholder', options.input[oi].placeholder);
                                    }
                                    if(v) {
                                        obj.val(v);
                                    }
                                    if (options.input[oi].size) {
                                        var sz = options.input[oi].size <= 1 ? (options.input[oi].size * 100) + '%' : sz + 'px';
                                        obj.css('width', sz);
                                    }
                                    if (!obj.hasClass('hasevent')) {
                                        obj.addClass('hasevent');
                                        obj.bind('change', function () {
                                            var nav = $(this).closest('.chart_navigation');
                                            if (nav) {
                                                var query = $(this).attr('query');
                                                var chartId = nav.attr('chartId');
                                                // set new query
                                                minfin_data[chartId].options.api.query[query] = $(this).val();
                                                // reload
                                                reloadnow(nav.attr('chartId'));
                                            } else {
                                                console.warn('chartId missing in chart_navigation');
                                            }
                                        });
                                    }
                                }
                            }
                        }
                    }
                    // selects
                    if (options.select && options.select.length) {
                        for (var os in options.select) {
                            // get setting from querystring
                            // select content must be available in data
                            if (options.select[os] && options.select[os].type && options.select[os].query) {
                                var t = options.select[os].type;
                                var q = options.select[os].query;
                                var o = tmp_data[q] ? tmp_data[q] : [];
                                var d = options.api['query'][q];
                                var obj = $('#' + options.target + '_navigation #select_' + t);
                                if (!obj.length) obj = $('#' + options.target + '_navigation_top #select_' + t);
                                if (obj) {
                                    obj.attr('query', q);
                                    // transfer year object to array
                                    if (typeof (o) == 'string') {
                                        o = [];
                                    }
                                    if (!o.length && typeof (o) == 'object') {
                                        var to = [];
                                        for (var i in o) {
                                            to.push(i);
                                        }
                                        o = to;
                                    }
                                    if (!o.length && options.select[os].options) {
                                        o = [];
                                        for (var i in options.select[os].options) {
                                            o.push(options.select[os].options[i]);
                                        }
                                    }
                                    var optionsY = '';
                                    if (options.select[os].first) {
                                        var firstValue = options.select[os].firstValue ? options.select[os].firstValue : ''
                                        optionsY += '<option value="' + firstValue + '">' + options.select[os].first + '</option>';
                                    }
                                    for (var i in o) {
                                        var selected = o[i] == d ? ' selected' : ''
                                        var tekst = options.select[os].query == 'ministerie' && ministerie_abbreviation[o[i].toUpperCase()] ? ministerie_abbreviation[o[i].toUpperCase()] + ' (' + o[i] + ')' : o[i];
                                        var exclude = false;
                                        if (options.select[os]['exclude'] && options.api && options.api.query && options.api.query[options.select[os]['exclude']] && o[i] == options.api.query[options.select[os]['exclude']]) {
                                            //
                                        } else {
                                            optionsY += '<option value="' + o[i] + '"' + selected + '>' + tekst + '</option>';
                                        }
                                    }
                                    obj.html(optionsY);
                                    if (options.select[os].size) {
                                        var sz = options.select[os].size <= 1 ? (options.select[os].size * 100) + '%' : sz + 'px';
                                        obj.css('width', sz);
                                    }
                                    if (!obj.hasClass('hasevent')) {
                                        // preset title & multiplier
                                        if (options.select[os]['query'] && options.api && options.api.query && options.api.query[options.select[os]['query']] && options.select[os]['title']) {
                                            options.title = options.select[os]['title'][options.api.query[options.select[os]['query']]];
                                            options['page_title'] = typeof (tmp_data['page_title']) != 'undefined' ? tmp_data['page_title'] : options['page_title'] ? options['page_title'] : '';
                                            options['legend_title'] = typeof (tmp_data['legend_title']) != 'undefined' ? tmp_data['legend_title'] : options['title'];
                                        }
                                        if (options.select[os]['query'] && options.api && options.api.query && options.api.query[options.select[os]['query']] && options.select[os]['multiplier']) {
                                            options.multiplier = options.select[os]['multiplier'][options.api.query[options.select[os]['query']]];
                                        }
                                        obj.addClass('hasevent');
                                        obj.bind('change', { os: os, options: options }, function (e) {
                                            var nav = $(this).closest('.chart_navigation');
                                            if (nav) {
                                                var query = $(this).attr('query');
                                                var chartId = nav.attr('chartId');
                                                if (typeof (nav.attr('chartId')) != 'undefined' ) {
                                                    // set new query
                                                    minfin_data[chartId].options.api.query[query] = $(this).val();
                                                    // change title, currency & multiplier
                                                    var opt = minfin_data[chartId].options;
                                                    if (opt && opt.select) {
                                                        for (var os in opt.select) {
                                                            if (opt.select[os].query && opt.select[os].query == query && opt.select[os].currency) {
                                                                minfin_data[chartId].options.currency = opt.select[os].currency[$(this).val()];
                                                            }
                                                            // reset title & multiplier
                                                            if (opt.select[os].query && opt.select[os].query == query && opt.select[os].title) {
                                                                minfin_data[chartId].options.title = opt.select[os].title[$(this).val()];
                                                            }
                                                            if (opt.select[os].query && opt.select[os].query == query && opt.select[os].multiplier) {
                                                                minfin_data[chartId].options.multiplier = opt.select[os].multiplier[$(this).val()];
                                                            }
                                                            // reset slider
                                                            if (options.slider && options.slider.reset && typeof (options.slider.reset[opt.select[os].query]) != 'undefined' && typeof (options.api.query[opt.select[os].query]) != 'undefined') {
                                                                if (!options.slider.reset[opt.select[os].query]) {
                                                                    //options.slider.reload[opt.select[os].query] = options.api.query[opt.select[os].query];
                                                                }
                                                                if (options.slider.reset[opt.select[os].query] != options.api.query[opt.select[os].query]) {
                                                                    options.slider.reset[opt.select[os].query] = options.api.query[opt.select[os].query];
                                                                    if (options.api.query['min']) options.api.query['min'] = false;
                                                                    if (options.api.query['max']) options.api.query['max'] = 1000000000000000;
                                                                }

                                                            }
                                                        }
                                                    }
                                                    // reload
                                                    reloadnow(nav.attr('chartId'));
                                                } else if (e.data.options && typeof (e.data.options.select[e.data.os]['script']) != 'undefined') {
                                                    executeFunctionByName(e.data.options.select[e.data.os]['script'], window, $(this).val(), e.data.options);
                                                }
                                            } else {
                                                console.warn('chartId missing in chart_navigation');
                                            }
                                        });
                                    }

                                    // show
                                    if (document.querySelector('#' + options.target + '_navigation_top #navigation_top_select') && options && options.align) {
                                        document.querySelector('#' + options.target + '_navigation_top #navigation_top_select').style.textAlign = options.align;
                                    }
                                    if (document.querySelector('#' + options.target + '_navigation_top #navigation_top_select')) {
                                        document.querySelector('#' + options.target + '_navigation_top #navigation_top_select').style.display = 'block';
                                    }
                                    if (document.querySelector('#' + options.target + '_navigation_top')) {
                                        document.querySelector('#' + options.target + '_navigation_top').style.display = 'block';
                                    }

                                    if (document.querySelector('#' + options.target + '_navigation #navigation_select') && options && options.align) {
                                        document.querySelector('#' + options.target + '_navigation #navigation_select').style.textAlign = options.align;
                                    }
                                    if (document.querySelector('#' + options.target + '_navigation #navigation_select')) {
                                        document.querySelector('#' + options.target + '_navigation #navigation_select').style.display = 'block';
                                    }
                                    if (document.querySelector('#' + options.target + '_navigation')) {
                                        document.querySelector('#' + options.target + '_navigation').style.display = 'block';
                                    }
                                }
                            } else if (options.select[os] && options.select[os].type && options.select[os].type == 'year' && available_years.length > 1) {
                    // general year select
                    // content comes from available_years
                                var dropDownY = document.querySelector('#' + options.target + '_navigation #select_year');
                                if (dropDownY) {
                                    var optionsY = '';
                                    for (let p in available_years) {
                                        var selected = available_years[p] == minfin_api.path['year'] ? ' selected' : ''
                                        optionsY += '<option value="' + available_years[p] + '"' + selected + '>' + available_years[p] + '</option>';
                                    }
                                    dropDownY.innerHTML = optionsY;
                                    if (!dropDownY.classList.contains('hasevent')) {
                                        dropDownY.classList.add('hasevent');
                                        dropDownY.addEventListener('change', function () {
                                            setDrupalPath('year', this.value);
                                            var nav = $(this).closest('.chart_navigation');
                                            if (nav) {
                                                reloadnow(nav.attr('chartId'));
                                            } else {
                                                console.warn('chartId missing in chart_navigation');
                                            }
                                        });
                                    }
                                    // show
                                    document.querySelector('#' + options.target + '_navigation #navigation_select').style.textAlign = 'center';
                                    if (document.querySelector('#' + options.target + '_navigation #navigation_select') && options && options.align) {
                                        document.querySelector('#' + options.target + '_navigation #navigation_select').style.textAlign = options.align;
                                    }
                                    document.querySelector('#' + options.target + '_navigation #navigation_select').style.display = 'block';
                                    document.querySelector('#' + options.target + '_navigation').style.display = 'block';
                                }
                            }
                        }
                    }
                }

                // add slider
                if (options.slider && options.slider.id) {
                    options.slider['obj'] = slider.init(options.slider.id, {
                        type: options.slider.type ? options.slider.type : 'single',
                        size: options.slider.size ? options.slider.size : 1,
                        min: options.slider.min ? options.slider.round ? Math.floor(options.slider.min) : options.slider.min : 0,
                        max: options.slider.max ? options.slider.round ? Math.ceil(options.slider.max) : options.slider.max : 0,
                        start: options.slider.start ? options.slider.round ? Math.floor(options.slider.start) : options.slider.start : 0,
                        end: options.slider.end ? options.slider.round ? Math.ceil(options.slider.end) : options.slider.end : 0,
                        step: options.slider.step ? options.slider.step : 1,
                        round: options.slider.round ? options.slider.round : true,
                        currency: options.slider.currency ? options.slider.currency : '',
                        divider: options.divider ? options.divider : 1,
                    }, options.slider.script);
                }

                // assign navigation bar
                var target = typeof (options.target) == 'undefined' ? 'chart_canvas' : options.target;
                /*
                navigation('chart_navigation', [{
                    id: target,
                    options: 'none', //
                    back: 'identifier'
                }]);
                */

                // set meta.previous and add click event
                var link = document.querySelector('#' + options.target + '_navigation #chart_nav_return');
                if (link) {
                    if (!link.hasAttribute('back')) {
                        link.addEventListener('click', function (e) {
                            //console.log(minfin_api['drupal_structure'], minfin_api['path']);
                            var index = 0;
                            for (var ds in minfin_api['drupal_structure']) {
                                if (minfin_api['path'][minfin_api['drupal_structure'][ds]]) {
                                    index = ds;
                                }
                            }
                            //console.log(index, minfin_api['drupal_structure'][index])
                            delete minfin_api['path'][minfin_api['drupal_structure'][index]];
                            var nav = $(this).closest('.chart_navigation');
                            if (nav) {
                                reloadnow(nav.attr('chartId'));
                            } else {
                                console.warn('chartId missing in chart_navigation');
                            }
                        });
			            //document.querySelector('#' + options.target  +'_navigation').style.display = 'block';
                    }
                    if (typeof (tmp_data['identifier']) != 'undefined') {
                        link.innerHTML = 'Terug' + ((typeof (tmp_data['back_title']) != 'undefined' && tmp_data['back_title'] != '') ? ' naar ' + tmp_data['back_title'] : '');
                        link.style.visibility = 'visible';
                        link.setAttribute('back', tmp_data['identifier']);
                        link.setAttribute('title', link.innerHTML);
                    } else {
                        link.style.visibility = 'hidden';
                        link.setAttribute('back', '');
                        link.setAttribute('title', '');
                    }
                }

                // colour stuff
                // add color exception
                if ((typeof (options.use_identifiers_for_colors) == 'undefined' || options.use_identifiers_for_colors == true) && (!minfin_api.path['identifier'] || minfin_api.path['identifier'] == '0')) {
                    var except_colors = [];
                    for (let i in data[0]) {
                        var c = ministerie_identifiers[data[0][i]['identifier']] ? ministerie_identifiers[data[0][i]['identifier']] : false;
                        if (!c) c = ministerie_colors[data[0][i]['title']] ? ministerie_colors[data[0][i]['title']] : colorset[i];
                        except_colors.push(c);
                    }
                    am4core.settings({
                        colors: except_colors
                    });
                } else {
                    chart_colors = [];
                    for (let c in colorset) {
                        chart_colors.push(colorset[c]);
                    }
                    am4core.settings({
                        colors: chart_colors
                    });
                }

                // save data for reload
                for (var d in data) {
                    //csv_data.push(data[d]);
                }

                var target = typeof (data[1].target) == 'undefined' ? 'chart_canvas' : data[1].target;
                //console.log(data)

                // TEST
                for (test in data[0]) {
                    //data[0][test]['description'] = 'mek';
                    //console.log(889);
                }

                if ($('#' + target).length) {
                    minfin_chart(target, data[1].graph ? data[1].graph : 'pie', data[0], data[1]);
                }
            }
            function handle_csv_data(tmp_data) {
                // set colors if available in options
                if (csv_options.colors) {
                    if (csv_options.colors == 'reversed') {
                        csv_options.colors = [];
                        for (var c = minfin_colors.length - 1; c >= 0; c--) {
                            csv_options.colors.push(minfin_colors[c]);
                        }
                    } else {
                        var cols = [];
                        for (var c in csv_options.colors) {
                            cols.push(get_minfin_color(csv_options.colors[c]));
                        }
                        csv_options.colors = cols;
                    }
                    minfin_colors = csv_options.colors;
                }

                // seek available years
                for (var y in tmp_data) {
                    if (tmp_data[y]['Jaar']) {
                        if (csv_values.indexOf(tmp_data[y]['Jaar']) == -1) {
                            csv_values.push(tmp_data[y]['Jaar']);
                        }
                        // fill years in all corresponding rows
                        var found = -1;

                        tmp_data[y]['Bedrag'] = tmp_data[y]['Bedrag'].replace(/[\ \t\.]/g, '');
                        if (tmp_data[y]['Bedrag'] == '-') tmp_data[y]['Bedrag'] = 0;

                        for (var r in tmp_data) {
                            if (tmp_data[r]['Titel'] == tmp_data[y]['Titel'] && tmp_data[r]['Hoofdstuk'] == tmp_data[y]['Hoofdstuk'] && tmp_data[r]['Maatregel'] == tmp_data[y]['Maatregel'] && tmp_data[r]['Specificatie'] == tmp_data[y]['Specificatie']) {
                                tmp_data[y][tmp_data[r]['Jaar']] = tmp_data[r]['Bedrag'];
                                tmp_data[y][tmp_data[r]['Jaar'] + '_info'] = tmp_data[r]['Toelichting_bedrag'] ? tmp_data[r]['Toelichting_bedrag'] : '';
                                tmp_data[y][tmp_data[r]['Jaar'] + '_toelichting'] = tmp_data[r]['Toelichting'] ? tmp_data[r]['Toelichting'] : '';
                                tmp_data[y][tmp_data[r]['Jaar'] + '_link'] = tmp_data[r]['Link'] ? tmp_data[r]['Link'] : ''
                                tmp_data[y][tmp_data[r]['Jaar'] + '_status'] = tmp_data[r]['Status'] ? tmp_data[r]['Status'] : ''
                                if (found > -1) {
                                    tmp_data[r]['Titel'] = '-1';
                                }
                                found = r;
                            }
                        }
                    }
                }

                // sort csv_values (year) values if the are actually years
                var years = true;
                for (var v in csv_values) {
                    var testval = parseInt(csv_values[v]);
                    if (!testval || testval < 1970 || testval > 2040) years = false;
                }

                if (years) {
                    sortVal[1] = 'asc';
                    sortVal[0] = csv_values;
                    csv_values.sort(sortArray);
                    minfin_api.available_years[api_trigger] = csv_values;
                }

                // remove obsolete
                for (var d in tmp_data) {
                    if (tmp_data[d]['Titel'] == '-1') {
                        tmp_data.splice(d, 1);
                    }
                }
                // add rows for totals
                var comp = ['', '', '', '']
                var data = [];
                for (var d in tmp_data) {
                    if (tmp_data[d]['Titel'] && comp[0] != tmp_data[d]['Titel'] && tmp_data[d]['Titel'] != '') {
                        comp[0] = tmp_data[d]['Titel'];
                        if (tmp_data[d]['Hoofdstuk'] == '') {
                            csv_date = tmp_data[d]['Status'];
                            csv_bedrag = typeof (tmp_data[d]['Bedrag']) != 'undefined' ? tmp_data[d]['Bedrag'] : false;
                            csv_toelichting_bedrag = typeof (tmp_data[d]['Toelichting_bedrag']) != 'undefined' ? tmp_data[d]['Toelichting_bedrag'] : false;
                            comp[1] = '';
                            comp[2] = '';
                            comp[3] = '';
                            tmp_data[d]['Nummer_begroting'] = '0';
                            data.push(tmp_data[d]);
                        } else {
                            data.push({ 'Titel': comp[0], 'Hoofdstuk': '', 'Maatregel': '', 'Specificatie': '', 'Nummer_begroting': '0', 'Link': '', 'Toelichting': '', 'Status': '' });
                        }
                    }
                    if (tmp_data[d]['Hoofdstuk'] && comp[1] != tmp_data[d]['Hoofdstuk'] && tmp_data[d]['Hoofdstuk'] != '') {
                        comp[1] = tmp_data[d]['Hoofdstuk'];
                        if (tmp_data[d]['Maatregel'] == '') {
                            comp[2] = '';
                            comp[3] = '';
                            tmp_data[d]['Nummer_begroting'] = tmp_data[d]['Nummer_begroting'] != '' ? tmp_data[d]['Nummer_begroting'] : '';
                            tmp_data[d]['Titel'] = '';
                            data.push(tmp_data[d]);
                        } else {
                            comp[2] = '';
                            comp[3] = '';
                            data.push({ 'Titel': '', 'Hoofdstuk': comp[1], 'Maatregel': '', 'Specificatie': '', 'Nummer_begroting': tmp_data[d]['Nummer_begroting'], 'Link': '', 'Status': '' });
                        }
                    }
                    if (tmp_data[d]['Maatregel'] && comp[2] != tmp_data[d]['Maatregel'] && tmp_data[d]['Maatregel'] != '') {
                        comp[2] = tmp_data[d]['Maatregel'];
                        if (tmp_data[d]['Specificatie'] == '' || tmp_data[d]['Specificatie'] == '0' || tmp_data[d]['Specificatie'] == '-') {
                            comp[3] = '';
                            tmp_data[d]['Specificatie'] = '';
                            tmp_data[d]['Nummer_begroting'] = '';
                            tmp_data[d]['Titel'] = '';
                            tmp_data[d]['Hoofdstuk'] = '';
                            data.push(tmp_data[d]);
                        } else {
                            comp[3] = '';
                            data.push({ 'Titel': '', 'Hoofdstuk': '', 'Maatregel': comp[2], 'Specificatie': '', 'Nummer_begroting': '', 'Link': '', 'Status': '' });
                        }
                    }
                    if (tmp_data[d]['Specificatie'] && comp[3] != tmp_data[d]['Specificatie'] && tmp_data[d]['Specificatie'] != '' && tmp_data[d]['Specificatie'] != '0' && tmp_data[d]['Specificatie'] != '-') {
                        comp[3] = tmp_data[d]['Specificatie'];
                        tmp_data[d]['Titel'] = '';
                        tmp_data[d]['Nummer_begroting'] = '';
                        tmp_data[d]['Hoofdstuk'] = '';
                        tmp_data[d]['Maatregel'] = '';
                        data.push(tmp_data[d]);
                    }
                }
                tmp_data = []
                for (d in data) {
                    tmp_data.push(data[d])
                }

                // add unique id's
                var new_id = [0, 'A', 0]; // hoofdstuk; maatregel; specificatie
                for (var csv_id in tmp_data) {
                    if (tmp_data[csv_id].Nummer_begroting != '') {
                        new_id = [tmp_data[csv_id].Nummer_begroting, String.fromCharCode(64), 0];
                    } else {
                        if (tmp_data[csv_id].Titel != '') {
                            tmp_data[csv_id].Nummer_begroting = '0';
                            //console.log('Titel', new_id)
                        } else if (tmp_data[csv_id].Specificatie != '') {
                            new_id[2]++;
                            if (new_id[1].charCodeAt(0) == 64) new_id[1] = String.fromCharCode(new_id[1].charCodeAt(0) + 1);
                            tmp_data[csv_id].Nummer_begroting = new_id[0] + new_id[1] + new_id[2];
                            //console.log('Specificatie', new_id)
                        } else if (tmp_data[csv_id].Maatregel != '') {
                            new_id[1] = String.fromCharCode(new_id[1].charCodeAt(0) + 1);
                            new_id[2] = 0;
                            tmp_data[csv_id].Nummer_begroting = new_id[0] + new_id[1];
                            //console.log('Maatregel', new_id)
                        } else if (tmp_data[csv_id].Hoofdstuk != '') {
                            new_id = ['' + (parseInt(new_id[0]) + 1), String.fromCharCode(64), 0];
                            tmp_data[csv_id].Nummer_begroting = new_id[0];
                            //console.log('Hoofdstuk', new_id)
                            //console.log(tmp_data[csv_id].Nummer_begroting)
                        }
                    }
                }

                // reorganise data
                var csv_totals = [0, 0, 0, 0];
                var csv_column = 0;
                var collect = false;

                // calc totals
                // reverse data
                var reversed = [];
                for (var c = tmp_data.length; c >= 0; c--) {
                    if (tmp_data[c]) {
                        reversed.push(tmp_data[c]);
                    }
                }
                // numberise, calculate totals and multiply
                var multiplier = 1; // csv_options.multiplier ? csv_options.multiplier : 1;

                for (var level = 3; level >= 0; level--) {
                    var count = false;
                    var counter = 0;
                    for (var row in reversed) {
                        // calc totals for all valued columns
                        if (!collect && reversed[row][csv_structure[level]] != '') { // start collecting
                            collect = true;
                            for (var v = 0; v < csv_values.length; v++) {
                                csv_totals[v] = 0;
                            }
                        }
                        if (collect && level > 0 && reversed[row][csv_structure[level - 1]] != '') { // end collecting
                            collect = false;
                            for (var v = 0; v < csv_values.length; v++) {
                                var val = reversed[row][csv_values[v]];
                                if (!val || val == '') {
                                    reversed[row][csv_values[v]] = csv_totals[v] / multiplier;
                                }
                            }
                        }
                        if (collect && reversed[row][csv_structure[level]] != '') { // collect
                            for (var v = 0; v < csv_values.length; v++) {
                                var val = reversed[row][csv_values[v]];
                                if (!val || val == '') val = '0';
                                val = '' + val;
                                val = val.replace(',', '.');
                                val = parseFloat(val) * multiplier;
                                reversed[row][csv_values[v]] = val;
                                if (!reversed[row]['Extra'] || reversed[row]['Extra'].indexOf('minus') == -1) {
                                    csv_totals[v] += val;
                                }
                            }
                        }
                    }
                }

                // re-reverse
                tmp_data = [];
                for (var c = reversed.length; c >= 0; c--) {
                    if (reversed[c]) {
                        tmp_data.push(reversed[c]);
                    }
                }

                // save data for reload
                for (var d in tmp_data) {
                    //console.log(tmp_data[d])
                    csv_data.push(tmp_data[d]);
                }

                //console.log(JSON.stringify(tmp_data))
                tmp_data = get_data_from_csv(tmp_data);

                //csv_data.shift()
                var target = typeof (tmp_data[1].target) == 'undefined' ? 'chart_canvas' : tmp_data[1].target;
                if (target) {
                    minfin_chart(target, tmp_data[1].graph ? tmp_data[1].graph : 'pie', tmp_data[0], tmp_data[1]);
                }

            }

            function get_data_from_csv(tmp_data) {

                var data = [];
                var own = false;
                var csv_column = 0;
                var options = {}
                var collect = 0;
                var identifiers = [[], [], [], []];
                var parent_identifier = [];
                for (var row in tmp_data) {

                    // collect parent's id
                    if (tmp_data[row][csv_structure[0]] != '') identifiers[0] = [tmp_data[row]['Nummer_begroting'], tmp_data[row][csv_structure[0]]];
                    if (tmp_data[row][csv_structure[1]] != '') identifiers[1] = [tmp_data[row]['Nummer_begroting'], tmp_data[row][csv_structure[1]]];
                    if (tmp_data[row][csv_structure[2]] != '') identifiers[2] = [tmp_data[row]['Nummer_begroting'], tmp_data[row][csv_structure[2]]];
                    if (tmp_data[row][csv_structure[3]] != '') identifiers[3] = [tmp_data[row]['Nummer_begroting'], tmp_data[row][csv_structure[3]]];

                    if (tmp_data[row].Nummer_begroting == minfin_api.path['identifier']) {
                        // first row, initialise
                        if (tmp_data[row][csv_structure[0]] != '') csv_column = 1; // hfd
                        if (tmp_data[row][csv_structure[1]] != '') csv_column = 2; // maatreg
                        if (tmp_data[row][csv_structure[2]] != '') csv_column = 3; // spec
                        if (tmp_data[row][csv_structure[3]] != '') csv_column = 4;
                        if (csv_column > 1) parent_identifier = identifiers[csv_column - 2];
                        collect = 1;
                        // settings
                        var links = tmp_data[row]['Link'].split('|');
                        if (links.length && links[0] != '') {
                            for (var l in links) {

                                var info = links[l].trim();
                                var matches = info.match(/^\[([^\[\]]+)\]/, 'gi');
                                if (matches) {
                                    links[l] = links[l].replace(matches[0], '');
                                }

                                links[l] = { link: links[l].trim() }
                                if (matches) {
                                    links[l].description = matches[1];
                                }
                            }
                            links = { 'Publicaties': links };
                        } else {
                            links = '';
                        }

                        var title = ''; //minfin_api.map['nl'][minfin_api.path['vuo']] ? minfin_api.map['nl'][minfin_api.path['vuo']] : '';
                        title = cap(title);
                        title += ' ' + tmp_data[0][csv_structure[0]];
                        //title += ' per ' + tmp_data[0]['Status'];

                        var multiplier_text = csv_options.multiplier ? csv_options.multiplier : csv_options.divider ? csv_options.divider : 1000000;
                        multiplier_text = multiplier_text == 1000000 ? "Alle bedragen in miljoenen" : multiplier_text > 1 ? "Alle bedragen x" + multiplier_text : '';

                        options = {
                            links: links,
                            description: tmp_data[row]['Toelichting'] ? tmp_data[row]['Toelichting'] : '', //tmp_data[row][minfin_api.path['year'] + '_toelichting'] ? tmp_data[row][minfin_api.path['year'] + '_toelichting'] : '',
                            category: "title",
                            currency: "EUR",
                            decimals: 0,
                            disc: true,
                            divider: 1000000,
                            legend: true,
                            local: false,
                            round: "ceil",
                            title: tmp_data[row][csv_structure[csv_column - 1]],
                            page_title: title,
                            value: minfin_api.path['year'],
                            legend_title: tmp_data[row][csv_structure[csv_column - 1]] + '<br><span class="mln">' + multiplier_text + '</span>',
                            previous: parent_identifier[0],
                            back_title: parent_identifier[1]
                        }
                        for (let o in csv_options) {
                            options[o] = csv_options[o];
                        }
                        var extra = tmp_data[row]['Extra'] ? '' + tmp_data[row]['Extra'] : '';
                        own = {
                            children: [],
                            identifier: tmp_data[row]['Nummer_begroting'],
                            title: tmp_data[row][csv_structure[csv_column - 1]],
                            value: minfin_api.path['year'],
                            range: split_range(tmp_data[row]['Range'], options), // self
                            extra: options.shades ? extra + ' pattern' : extra,
                            estimation: '1',
                            status: tmp_data[row][minfin_api.path['year'] + '_info'] + (tmp_data[row]['Status'] != '' ? ' (' + tmp_data[row]['Status'] + ')' : '')
                        };
                        // add values
                        for (var v in csv_values) {
                            own[csv_values[v]] = tmp_data[row][csv_values[v]];
                        }
                        if (csv_column == 4) csv_column = 0;
                    } else if (collect) {
                        // get actual data_
                        if (tmp_data[row][csv_structure[csv_column]] != '') { // donut data
                            // default data
                            tmp_data[row]['Status'] = tmp_data[row][minfin_api.path['year'] + '_status'] ? tmp_data[row][minfin_api.path['year'] + '_status'] : '';
                            tmp_data[row]['Link'] = tmp_data[row][minfin_api.path['year'] + '_link'] ? tmp_data[row][minfin_api.path['year'] + '_link'] : '';
                            tmp_data[row]['Toelichtng'] = tmp_data[row][minfin_api.path['year'] + '_toelichtng'] ? tmp_data[row][minfin_api.path['year'] + '_toelichtng'] : '';
                            if (!tmp_data[row][minfin_api.path['year'] + '_info']) tmp_data[row][minfin_api.path['year'] + '_info'] = '';

                            var extra = tmp_data[row]['Extra'] ? '' + tmp_data[row]['Extra'] : '';
                            var obj = {
                                children: [],
                                self_info: true,
                                child_info: false,
                                range: split_range(tmp_data[row]['Range'], options), // obj
                                extra: options.shades ? extra + ' pattern' : extra,
                                estimation: '1',
                                link: 'internal:' + api_trigger + '/' + minfin_api.path['year'] + '/' + minfin_api.path['vuo'] + '/' + tmp_data[row]['Nummer_begroting'],
                                identifier: tmp_data[row]['Nummer_begroting'],
                                title: tmp_data[row][csv_structure[csv_column]],
                                value: minfin_api.path['year'],
                                status: tmp_data[row][minfin_api.path['year'] + '_info'] + (tmp_data[row]['Status'] != '' ? ' (' + tmp_data[row]['Status'] + ')' : '')
                            };
                            // test and change link to external if needed
                            if (tmp_data[row]['Link'] && tmp_data[row]['Link'].indexOf('ext:') == 0) {
                                obj.link = tmp_data[row]['Link'];
                            }
                            // Is there a info to show after click
                            if ((tmp_data[row]['Toelichting'] && tmp_data[row]['Toelichting'] != '') || (tmp_data[row]['Link'] && tmp_data[row]['Link'] != '')) {
                                obj.self_info = true;
                            }
                            // add values
                            for (var v in csv_values) {
                                obj[csv_values[v]] = tmp_data[row][csv_values[v]];
                            }
                            // add extra % line data
                            if (typeof (csv_options['linedata']) != 'undefined' && typeof (tmp_data[row][csv_options['linedata']]) != 'undefined') {
                                obj[csv_options['linedata']] = parseFloat(tmp_data[row][csv_options['linedata']]);
                            }
                            own = false;
                            data.push(obj);
                            collect++;
                        } else if (csv_column && csv_column < 3 && tmp_data[row][csv_structure[csv_column + 1]] != '') { // child data
                            var child = {
                                title: tmp_data[row][csv_structure[csv_column + 1]],
                                identifier: tmp_data[row]['Nummer_begroting'],
                                amount: tmp_data[row][minfin_api.path['year']],
                                range: split_range(tmp_data[row]['Range'], options), // child
                            }
                            // Is there a child with info
                            if ((tmp_data[row]['Toelichting'] && tmp_data[row]['Toelichting'] != '') || (tmp_data[row]['Link'] && tmp_data[row]['Link'] != '')) {
                                obj.child_info = true;
                            }
                            obj.children.push(child);
                        } else if (tmp_data[row][csv_structure[csv_column - 1]] != '') { // reset  {
                            if (collect == 1) {
                                data.push(own);
                            }
                            collect = 0;
                        }
                    }
                }
                if (collect == 1) {
                    data.push(own);
                }

                // add curyear
                for (var d in data) {
                    if (minfin_api.path['year'] && data[d][minfin_api.path['year']]) {
                        data[d]['curyear'] = true;
                    }
                }

                if (options.links != '') {
                    set_links(options.links);
                }
                if (options.description != '') {
                    set_description(options.description);
                }

                // first fill selects
                //TODO: make navigation chart dependent
                //TODO: minfin_api must be independent too
                if (minfin_api['available_years'][api_trigger].length > 1) {
                    var dropDownY = document.getElementById('select_year');
                    if (dropDownY) {
                        var optionsY = '';
                        for (let p in minfin_api['available_years'][api_trigger]) {
                            var selected = minfin_api['available_years'][api_trigger][p] == minfin_api.path['year'] ? ' selected' : ''
                            optionsY += '<option value="' + minfin_api['available_years'][api_trigger][p] + '"' + selected + '>' + minfin_api['available_years'][api_trigger][p] + '</option>';
                        }
                        dropDownY.innerHTML = optionsY;
                        dropDownY.addEventListener('change', function () {
                            setDrupalPath('year', this.value);
                            reloadnow(0);
                        });
                        // show
                        document.getElementById('navigation_select').style.display = 'block';
                    }
                }

                var target = typeof (options.target) == 'undefined' ? 'chart_canvas' : options.target;
                navigation('chart_navigation', [{
                    id: target,
                    options: 'none', //
                    back: 'identifier'
                }]);

                // set meta.previous
                //TODO: make navigation chart dependent
                //TODO: minfin_api must be independent too
                var link = document.getElementById('chart_nav_return');
                if (!link.hasAttribute('back')) {
                    link.addEventListener('click', function (e) {
                        var back = this.getAttribute('back');
                        if (back == 'refback') {
                            window.history.go(-1);
                        } else if (back != '') {
                            setDrupalPath('identifier', back);
                            reloadnow(0);
                        }
                    });
                }
                if (options.previous && options.previous != '') {
                    if (link) link.innerHTML = 'Terug naar ' + options.back_title;
                    link.style.visibility = !options.previous ? 'hidden' : 'visible';
                    link.setAttribute('back', options.previous);
                    link.setAttribute('title', 'Terug naar ' + options.back_title);
                } else if (window.history.length && (typeof (options.backlink) == 'undefined' || options.backlink)) {
                    if (link) link.innerHTML = 'Terug';
                    link.style.visibility = 'visible';
                    link.setAttribute('back', 'refback');
                    link.setAttribute('title', 'Terug');
                } else {
                    link.style.visibility = 'hidden';
                    link.setAttribute('back', '');
                    link.setAttribute('title', '');
                }

                // colour stuff
                // add color exception
                if ((typeof (options.use_identifiers_for_colors) == 'undefined' || options.use_identifiers_for_colors == true) && minfin_api.path['identifier'] == '0') {
                    var except_colors = [];
                    for (let i in data) {
                        var c = ministerie_identifiers[data[i]['identifier']] ? ministerie_identifiers[data[i]['identifier']] : false;
                        if (!c) c = ministerie_colors[data[i]['title']] ? ministerie_colors[data[i]['title']] : '#a90061';
                        except_colors.push(c);
                    }
                    am4core.settings({
                        colors: except_colors
                    });
                } else {
                    chart_colors = [];
                    for (let c in minfin_colors) {
                        chart_colors.push(minfin_colors[c]);
                    }
                    am4core.settings({
                        colors: chart_colors
                    });
                }
                return new Array(data, options);
            }

            function reloadnow(chartId) {

                //TODO, make reload graph independend

                var data = minfin_data[chartId].data;
                var options = minfin_data[chartId].options;

                csv_options = options; // temporary

                var chartname = charts[chartId].domId;

                // clean chart stuff
                if (discs[chartId] && typeof (discs[chartId].dispose) == 'function') {
                    discs[chartId].dispose();
                    discs[chartId] = false;
                }
                delete discs[chartId];
                if (containers[chartId]) {
                    containers[chartId].dispose();
                }
                delete containers[chartId];
                delete labels[chartId];
                for (s in series) {
                    if (series[s].chartId == chartId) {
                        series[s].dispose();
                        series[s] = false;
                    }
                }
                if (typeof (charts[chartId].dispose) == 'function') {
                    charts[chartId].dispose();
                }
                delete charts[chartId];
                charts[chartId] = false;

                // clear dom
                if (document.getElementById(chartname + "_legend")) {
                    document.getElementById(chartname + "_legend").innerHTML = '';
                    if (document.getElementById("description")) {
                        document.getElementById("description").innerHTML = ''; //TODO add chartname
                    }
                    if (document.getElementById("links")) {
                        document.getElementById("links").innerHTML = ''; //TODO add chartname
                    }
                }

                // test version
                var api = typeof (csv_options['api']) != 'undefind' ? csv_options['api'] : false;
                if (typeof (api['reload']) != 'undefined' && api['reload']) {
                    // newer version, always reload api
                    if (typeof (api['type']) != 'undefined' && api['type'] == 'json' && csv_options && csv_options.api && csv_options.api.query) {
                        readAPIFile(handle_json_data, csv_options, false, true); // not reloading and skip data from querystring
                    } else if (typeof (api['type']) != 'undefined' && api['type'] == 'json') {
                    readAPIFile(handle_json_data, csv_options, true);
                    } else if (typeof (api['type']) != 'undefined' && api['type'] == 'csv') {
                        readAPIFile(handle_csv_data, csv_options);
                    } else {
                        console.warn('API type unsupported in reload()');
                    }
                } else if (api['url'] && api['url'].indexOf('uitgavenplafonds') > -1) {
                    //console.log(csv_options)
                    readAPIFile(handle_json_data, csv_options, true);
                    //handle_json_data(csv_data, csv_options)
                    //readTextFile('corona-uitgavenplafond-2.json', handle_json_data, csv_options);
                } else if (api['url'] && api['url'].indexOf('tijdlijn') > -1) {
                    //console.log(csv_options)
                    readAPIFile(handle_json_data, csv_options, true);
                    //readTextFile('corona-tijdlijn-2.json', handle_json_data, csv_options);
                } else if (api['type'] && api['type'] == 'json' && options && options.api && options.api.handling) {
                    readAPIFile(handle_json_data, options, false);
                } else if (api['type'] && api['type'] == 'json') {
                    readAPIFile(handle_json_data, csv_options, true);
                } else {
                    // older version, use csv from memory
                    var tmp_data = get_data_from_csv(csv_data);
                    var target = typeof (tmp_data[1].target) == 'undefined' ? 'chart_canvas' : tmp_data[1].target;
                    minfin_chart(target, tmp_data[1].graph ? tmp_data[1].graph : 'pie', tmp_data[0], tmp_data[1]);
                }
            }
                        function isTouchDevice() {
                            return ('ontouchstart' in window || 'onmsgesturechange' in window);
                        };

            /*
            // Additional scripts
            */
            function cap(str) {
                return str.charAt(0).toUpperCase() + str.slice(1);
            }
            function set_manual_bars(chartId, action, key) {

                // add click event to close menu
                if (!$('body').attr('manual_selector')) {
                    $(document).bind('click', function () {
                        if ($('body').attr('manual_selector') && $('body').attr('manual_selector') != 'active') {
                            $('#' + $('body').attr('manual_selector') + '_navigation .manual_selector').removeClass('active');
                            $('#' + $('body').attr('manual_selector') + '_navigation .manual_selector .selector').css('display', 'none');
                        }
                    });
                }
                // close menu if open
                if ($('body').attr('manual_selector') && $('body').attr('manual_selector') != 'active') {
                    $('#' + $('body').attr('manual_selector') + '_navigation .manual_selector').removeClass('active');
                    $('#' + $('body').attr('manual_selector') + '_navigation .manual_selector .selector').css('display', 'none');
                }
                // set default
                $('body').attr('manual_selector', 'active');

                // init
                if (typeof (action) == 'undefined') {
                    action = 'init';
                }
                if (typeof (key) == 'undefined') {
                    key = false;
                }

                // seek identifiers from data
                var select = [];
                var options = false;
                var id = false;
                var chartindex = -1;
                var ids = [];
                var oks = [];
                if (typeof (key) == 'string') key = [key];
                for (var c in charts) {
                    if (charts[c].domId == chartId) {
                        chartindex = c;
                        options = minfin_data[c].options;
                        for (var d in charts[c].data_) {
                            if (select.indexOf(charts[c].data_[d]['identifier']) == -1) {
                                select.push(charts[c].data_[d]['identifier']);
                                for (k in key) {
                                    if (key[k] == charts[c].data_[d]['identifier']) {
                                        oks[k] = true;
                                        //ids.push(key[k]);
                                        //key[k] = charts[c].data_[d];
                                    }
                                }
                            }
                        }
                    }
                }

                ids = key;
                var max = options.selectbox && options.selectbox['max'] ? options.selectbox['max'] : 1000;
                // add or delete
                for (id in ids) {
                    if (options.series) {
                        if (key && action == 'add') {
                            if (options.series.indexOf(ids[id]) == -1 && options.series.length < max) {
                                options.series.push(ids[id]);
                                if (options.selectbox && options.selectbox.script) {
                                    // hook
                                    try {
                                        executeFunctionByName(options.selectbox.script, window, 'add', chartId, ids[id]);
                                    } catch (error) {
                                        console.log('Missing function add_' + options.selectbox.script);
                                    }
                                }
                            }
                        } else if (key && action == 'delete') {
                            for (var b in options.series) {
                                if (options.series[b] == ids[id]) {
                                    options.series.splice(b, 1);
                                    if (options.selectbox && options.selectbox.script) {
                                        // hook
                                        try {
                                            executeFunctionByName(options.selectbox.script, window, 'remove', chartId, ids[id]);
                                        } catch (error) {
                                            console.log('Missing function remove_' + options.selectbox.script);
                                        }
                                    }
                                    break;
                                }
                            }
                        }
                    } else {
                        if (key && action == 'add') {
                            options.bars.push(ids[id]);
                        } else if (key && action == 'delete') {
                            for (var b in options.bars) {
                                if (options.bars[b] == ids[id]) {
                                    options.bars.splice(b, 1);
                                    break;
                                }
                            }
                        }
                    }
                }

                // fill select list
                var selector = options.selectbox && options.selectbox['id'] && options.selectbox['id'] ? '#' + options.selectbox['id'] : '.manual_selector';
                select.sort(function (a, b) {
                    return a.toLowerCase().localeCompare(b.toLowerCase());
                });
                var target = $('#' + chartId + '_navigation ' + selector + ' .selector');
                target.html('');
                if (select.length > 4) {
                    var inputobj = $('<input placeholder="Zoek..." type="text"/>');
                    inputobj.bind('click', function (e) {
                        e.stopPropagation();
                    });
                    inputobj.bind('keyup', { 'chartId': chartId }, function (e) {
                        var val = $(this).val().toLowerCase();
                        $('#' + e.data.chartId + '_navigation ' + selector + ' .selector .option').each(function () {
                            if (val.length > 1 && $(this).attr('value').toLowerCase().indexOf(val) == -1) {
                                $(this).addClass('hide');
                            } else {
                                $(this).removeClass('hide');
                            }
                        });

                    });
                    target.append(inputobj);
                }
                for (var s in select) {
                    var slctobj = $('<div class="option" value="' + select[s] + '">' + select[s] + '</div>');
                    if (options.series) {
                        if (options.series.indexOf(select[s]) > -1) {
                            slctobj.addClass('selected');
                        }
                    } else {
                        if (options.bars.indexOf(select[s]) > -1) {
                            slctobj.addClass('selected');
                        }
                    }
                    var compare = options.selectbox['compare'] && options.selectbox['compare'] == 'series' ? 'series' : 'bars';
                    if (options.selectbox && options.selectbox['max'] && options[compare] && options[compare].length >= options.selectbox['max']) {
                        slctobj.addClass('inactive');
                    } else {
                        slctobj.bind('click', { 'chartId': chartId }, function (e) {
                            e.stopPropagation();
                            if ($(this).hasClass('selected')) {
                                //set_manual_bars(e.data.chartId, 'delete', $(this).attr('value'));
                            } else {
                                set_manual_bars(e.data.chartId, 'add', $(this).attr('value'));
                            }
                        });
                    }
                    target.append(slctobj);
                }

                // set alignment
                $(target).parent().css('text-align', options.align ? options.align : 'left');

                // add pills to dom
                target = $('#' + chartId + '_navigation ' + selector + ' .choice');
                target.bind('click', { 'chartId': chartId }, function (e) {
                    e.stopPropagation();
                    $('#' + chartId + '_navigation ' + selector).addClass('active');
                    $('#' + chartId + '_navigation ' + selector + ' .selector').css('display', 'block');
                    $('body').attr('manual_selector', chartId);
                });
                target.html('');
                var plusobj = $('<div class="plus"></div>');
                target.append(plusobj);
                var temp = options.series ? options.series : options.bars;
                if (temp && temp.length) {
                    for (var b in temp) {
                        var optobj = $('<div class="option" value="' + temp[b] + '"></div>');
                        optobj.attr('tabindex', tabindexcounter);
                        tabindexcounter++;
                        var delobj = $('<div class="delete"></div>');
                        delobj.bind('click', { 'chartId': chartId }, function (e) {
                            e.stopPropagation();
                            set_manual_bars(e.data.chartId, 'delete', $(this).parent().attr('value'));
                        });
                        optobj.append(delobj);
                        var colorobj = $('<div class="color"></div>');
                        // we don't have the bar color yet, we'll fill it later via set_manual_indexcolors()
                        optobj.append(colorobj);
                        var txtobj = $('<span>' + temp[b] + '</span>');
                        optobj.append(txtobj);
                        target.append(optobj);
                    }
                } else if (options.selectbox) {
                    txtobj = $('<span>' + options.selectbox['placeholder'] + '</span>');
                    plusobj.append(txtobj);
                    target.append(txtobj);
                }

                //console.log(id, select, key, options, col);
                // needs reload
                if (key && (action == 'add' || action == 'delete')) {
                    reloadnow(chartindex);
                } else {
                    set_manual_indexcolors();
                }
            }
            function set_manual_indexcolors() {
                for (var c in charts) {
                    if (charts[c]) {
                        chartid = charts[c].domId;
                        var found = chartid;
                        var selector = minfin_data[c].options && minfin_data[c].options.selectbox && minfin_data[c].options.selectbox['selector'] ? '#' + minfin_data[c].options.selectbox['selector'] : '.manual_selector';
                        if ($('#' + chartid + '_navigation ' + selector).length) {
                            if (minfin_data[c].options.series && minfin_data[c].options.series.length == 0) {
                                $('#' + chartid).parent().css('top', '-2048');
                                $('#' + chartid).parent().css('position', 'absolute');
                                $('#' + chartid).parent().css('visibility', 'hidden');
                            }
                            for (var s in series) {
                                if (series[s]._className && series[s]._className == 'ColumnSeries' && series[s].chartId == c) {
                                    var title = series[s]._dataItem.component.dataFields.valueY;
                                    for (var d in charts[c].data_) {
                                        if (charts[c].data_[d]['title'] == title) {
                                            var id = charts[c].data_[d]['identifier'];
                                            var color = series[s]._dataItem.component.realFill._value;
                                            $('#' + chartid + '_navigation ' + selector + ' .choice .option').each(function () {
                                                found = false;
                                                var index = minfin_data[c].options.series ? minfin_data[c].options.series.indexOf($(this).attr('value')) : minfin_data[c].options.bars.indexOf($(this).attr('value'));
                                                if (index > -1) {
                                                    $(this).find('.color').css('background-color', minfin_data[c].options.colors_[index]);
                                                }
                                            });
                                            $('#' + chartid + '_navigation #card_container .card').each(function () {
                                                var index = minfin_data[c].options.series ? minfin_data[c].options.series.indexOf($(this).attr('value')) : minfin_data[c].options.bars.indexOf($(this).attr('value'));
                                                if (index > -1) {
                                                    $(this).find('.color').css('background-color', minfin_data[c].options.colors_[index]);
                                                }
                                            });
                                            var len = minfin_data[c].options.series ? minfin_data[c].options.series.length : minfin_data[c].options.bars.length;
                                            if (len) {
                                                $('#' + chartid).parent().css('top', '0');
                                                $('#' + chartid).parent().css('position', 'relative');
                                                $('#' + chartid).parent().css('visibility', 'visible');
                                            } else {
                                                $('#' + chartid).parent().css('top', '-2048');
                                                $('#' + chartid).parent().css('position', 'absolute');
                                                $('#' + chartid).parent().css('visibility', 'hidden');
                                            }
                                        }
                                    }
                                }
                            }
                        }
                        // if (found) {
                        //     $('#' + found).parent().css('top', '-2048');
                        //     $('#' + found).parent().css('position', 'absolute');
                        //     $('#' + found).parent().css('visibility', 'hidden');
                        // }
                    }
                }
            }

            /*
            // Slider class
            */
            slider = new function () {
                // setup
                this.target = false;
                this.type = 'double'; // simple, single, double
                this.min = {
                    abs: 0,
                    val: 2,
                    left: 0
                }
                this.max = {
                    abs: 100,
                    val: 7,
                    left: 0
                }
                this.drag = 0;
                this.left = 0;
                this.size = 1;
                this.total = 0;
                this.step = 1;
                this.round = true;
                this.options = {};
                this.func = false;
                this.style = 'currency';
                this.currency = 'EUR';
                this.decimals = 0;
                this.divider = 1;

                this.init = function (target_id, options, func) { //type, min, max, step, start, end, value) {
                    if (typeof (target_id) == 'string') {
                        this.target = '#' + target_id;
                    }
                    this.func = typeof (func) == 'string' ? func : false;
                    var turn = this.set(options);
                    if (options.size) {
                        $(this.target).css('width', options.size > 1 ? 'calc(' + options.size + 'px - 3%)' : (options.size * 100 - 3) + '%');
                    }

                    if (!$(this.target + ' .slider').length) {
                        var node = $('<div class="slider"></div>');
                        var bar = $('<div class="slider_values"></div>');
                        node.append(bar);
                        bar = $('<div class="slider_bar"></div>');
                        node.append(bar);
                        var dot = $('<div class="slider_dot min"></div>');
                        dot.bind('mousedown', { that: this }, function (event) {
                            $(this).addClass('active');
                            event.stopPropagation();
                            var that = event.data.that;
                            that.drag = 'min';
                            that.draw();
                            $(document).bind('mousemove', { that: that }, event.data.that.movehandler);
                            $(document).bind('mouseup', { that: that }, event.data.that.stophandler);
                            return false;
                        });
                        dot.bind('touchstart', { that: this }, function (event) {
                            $(this).addClass('active');
                            event.stopPropagation();
                            var that = event.data.that;
                            that.drag = 'min';
                            that.draw();
                            $(document).bind('touchmove', { that: that, passive: false }, event.data.that.movehandler);
                            $(document).bind('touchend', { that: that }, event.data.that.stophandler);
                            return false;
                        });
                        if (this.type != 'double' && !turn) {
                            dot.css('display', 'none');
                        }
                        node.append(dot);
                        dot = $('<div class="slider_dot max"></div>');
                        dot.bind('mousedown', { that: this }, function (event) {
                            $(this).addClass('active');
                            event.stopPropagation();
                            var that = event.data.that;
                            that.drag = 'max';
                            that.draw();
                            $(document).bind('mousemove', { that: that }, event.data.that.movehandler);
                            $(document).bind('mouseup', { that: that }, event.data.that.stophandler);
                            return false;
                        });
                        dot.bind('touchstart', { that: this }, function (event) {
                            $(this).addClass('active');
                            event.stopPropagation();
                            var that = event.data.that;
                            that.drag = 'max';
                            that.draw();
                            $(document).bind('touchmove', { that: that, passive: false }, event.data.that.movehandler);
                            $(document).bind('touchend', { that: that }, event.data.that.stophandler);
                            return false;
                        });
                        if (this.type != 'double' && turn) {
                            dot.css('display', 'none');
                        }
                        node.append(dot);
                        $(this.target).append(node);
                        $(this.target).css('display', 'block');
                    }

                    this.draw();
                    return this;
                }
                // set options
                this.set = function (options) {
                    if (typeof (options) == 'object') {
                        for (var o in options) {
                            switch (o) {
                                case 'divider':
                                case 'type':
                                case 'step':
                                case 'style':
                                case 'currency':
                                case 'decimals':
                                    this[o] = options[o];
                                    break;
                                case 'min':
                                case 'max':
                                    this[o].abs = options[o];
                                    break;
                                case 'start':
                                    this.min.val = options[o];
                                    break;
                                case 'end':
                                    this.max.val = options[o];
                                    break;
                                case 'value':
                                    this.min.val = options[o];
                                    this.max.val = options[o];
                                    break;
                                default:
                                    this.options[o] = options[o];
                                    break;
                            }
                        }
                    }
                    var turn = false;
                    if (this.min.abs > this.max.abs) {
                        var i = this.min.abs;
                        this.min.abs = this.max.abs;
                        this.max.abs = i;
                    }
                    if (this.step > this.max.abs - this.min.abs) {
                        this.step = this.max.abs - this.min.abs;
                    }
                    if (this.min.val > this.max.val) {
                        var i = this.min.val;
                        this.min.val = this.max.val;
                        this.max.val = i;
                        turn = true;
                    }
                    if (this.min.val < this.min.abs) {
                        this.min.val = this.min.abs;
                    }
                    if (this.max.val < this.min.abs) {
                        this.max.val = this.min.abs;
                    }
                    if (this.min.val > this.max.abs) {
                        this.min.val = this.max.abs;
                    }
                    if (this.max.val > this.max.abs) {
                        this.max.val = this.max.abs;
                    }
                    if (this.round) {
                        this.min.abs = Math.floor(this.min.abs);
                        this.max.abs = Math.ceil(this.max.abs);
                    }
                    if ($(this.target + ' .slider').length) {
                        if (this.type != 'double' && !turn) {
                            $(this.target + ' .slider_dot.min').css('display', 'none');
                        }
                        if (this.type != 'double' && turn) {
                            $(this.target + ' .slider_dot.max').css('display', 'none');
                        }
                        this.draw();
                    }
                    return turn;
                }
                // handlers
                this.movehandler = function (event) {
                    //event.stopPropagation();
                    var that = event.data.that;
                    if (event.type == 'touchmove') {
                        var touch = event.originalEvent.touches[0] || event.originalEvent.changedTouches[0];
                        var newx = (touch.pageX - that.left) / that.total;
                    } else {
                        var newx = (event.pageX - that.left) / that.total;
                    }
                    if (newx < 0) {
                        newx = 0;
                    }
                    if (newx > 1) {
                        newx = 1;
                    }
                    if (that.drag == 'min') {
                        that.min.val = newx * (that.max.abs - that.min.abs) + that.min.abs;
                        if (that.min.val > that.max.val) {
                            that.min.val = that.max.val;
                            $(that.target + ' .slider_dot.' + that.drag).removeClass('active');
                            if (that.type != 'double') {
                                $(that.target + ' .slider_dot.' + that.drag).css('display', 'none');
                            }
                            that.drag = 'max';
                            $(that.target + ' .slider_dot.' + that.drag).addClass('active').css('display', 'block');
                        }
                    } else if (that.drag == 'max') {
                        that.max.val = newx * (that.max.abs - that.min.abs) + that.min.abs;
                        if (that.min.val > that.max.val) {
                            that.max.val = that.min.val;
                            $(that.target + ' .slider_dot.' + that.drag).removeClass('active');
                            if (that.type != 'double') {
                                $(that.target + ' .slider_dot.' + that.drag).css('display', 'none');
                            }
                            that.drag = 'min';
                            $(that.target + ' .slider_dot.' + that.drag).addClass('active');
                            $(that.target + ' .slider_dot.' + that.drag).addClass('active').css('display', 'block');
                        }
                    }
                    that.draw();
                    return false;
                }
                this.stophandler = function (event) {
                    event.stopPropagation();
                    var that = event.data.that;
                    $(that.target + ' .slider_dot.' + that.drag).removeClass('active');
                    $(document).unbind('mousemove', that.movehandler);
                    $(document).unbind('mouseup', that.stophandler);
                    var ret = {}
                    if (that.type == 'double') {
                        ret = {
                            min: that.min.abs,
                            max: that.max.abs,
                            start: that.min.val,
                            end: that.max.val,
                            value: that.max.val - that.min.val,
                            options: that.options
                        };
                    } else if (that.type == 'single') {
                        var st = $(that.target + ' .slider_dot.min').css('display') == 'block' ? that.max.val : that.min.val;
                        var nd = $(that.target + ' .slider_dot.min').css('display') == 'block' ? that.min.val : that.max.val;
                        ret = {
                            min: that.min.abs,
                            max: that.max.abs,
                            start: st,
                            end: nd,
                            value: nd - st,
                            options: that.options
                        };
                    } else if (that.type == 'simple') {
                        ret = {
                            min: that.min.abs,
                            max: that.max.abs,
                            start: $(that.target + ' .slider_dot.min').css('display') == 'block' ? that.min.val : that.max.val,
                            end: $(that.target + ' .slider_dot.min').css('display') == 'block' ? that.min.val : that.max.val,
                            value: $(that.target + ' .slider_dot.min').css('display') == 'block' ? that.min.val : that.max.val,
                            options: that.options
                        };
                    }
                    that.execute(ret);
                }
                // set absolute values in pixels
                this.setAbs = function () {
                    this.min.left = Math.round(parseFloat($(this.target + ' .slider_bar').css('left')));
                    this.max.left = Math.round($(this.target + ' .slider_bar').width()) + this.min.left;
                    this.size = this.max.left - this.min.left;
                    this.left = Math.round($(this.target + ' .slider').offset().left);
                    this.total = Math.round($(this.target + ' .slider').width());
                }
                // scale slider
                this.draw = function () {
                    if (this.step) {
                        this.min.val = (Math.round((this.min.val - this.min.abs) / this.step) * this.step) + this.min.abs;
                        this.max.val = (Math.round((this.max.val - this.min.abs) / this.step) * this.step) + this.min.abs;
                    }
                    var left = parseInt((this.min.val - this.min.abs) / (this.max.abs - this.min.abs) * 100);
                    var length = parseInt((this.max.val - this.min.abs) / (this.max.abs - this.min.abs) * 100 - left);
                    $(this.target + ' .slider_bar').css('left', left + '%');
                    $(this.target + ' .slider_bar').css('width', length + '%');
                    $(this.target + ' .slider_dot.min').css('left', left + '%');
                    $(this.target + ' .slider_dot.max').css('left', (left + length) + '%');
                    if (this.type == 'simple') {
                        $(this.target + ' .slider_bar').css('opacity', 0);
                    } else {
                        $(this.target + ' .slider_bar').css('opacity', 1);
                    }
                    // set values
                    if (this.type == 'double') {
                        var txt = format_number(Math.round(this.min.val / this.divider), {
                            style: this.style,
                            currency: this.currency,
                            decimals: this.decimals
                        }) + ' - ' + format_number(Math.round(this.max.val / this.divider), {
                            style: this.style,
                            currency: this.currency,
                            decimals: this.decimals
                        })
                    } else if (this.type == 'single') {
                        var st = $(this.target + ' .slider_dot.min').css('display') == 'block' ? this.max.val : this.min.val;
                        var nd = $(this.target + ' .slider_dot.min').css('display') == 'block' ? this.min.val : this.max.val;
                        var txt = format_number(Math.round((nd - st) / this.divider), {
                            style: this.style,
                            currency: this.currency,
                            decimals: this.decimals
                        })
                    } else if (this.type == 'simple') {
                        var txt = format_number($(this.target + ' .slider_dot.min').css('display') == 'block' ? Math.round(this.min.val / this.divider) : Math.round(this.max.val / this.divider), {
                            style: this.style,
                            currency: this.currency,
                            decimals: this.decimals
                        })
                    }
                    if (this.min.val == this.max.val) {
                        $(this.target + ' .slider_values').html(format_number(Math.round(this.min.val / this.divider), {
                            style: this.style,
                            currency: this.currency,
                            decimals: this.decimals
                        }));
                    } else {
                        $(this.target + ' .slider_values').html(txt);
                    }
                    this.setAbs();
                }
                this.execute = function (pass) {
                    if (this.func) {
                        try {
                            executeFunctionByName(this.func, window, pass);
                        } catch (error) {
                            console.log('Missing return function ' + this.func + ' from slider');
                        }
                    }
                }
            }

            /*
            //
            // verzelfstandigingen
            // init
            //
            */

            function verzelfstandigingen() {
                readAPIFile(handle_json_data, {
                    anchor: 'verzelfstandiging-title',
                    api: {
                        query: { // as object for api call with querystring needs documentation
                            soort: 'fte',
                            ministerie: false,
                            jaar: false
                        },
                        structure: ['dummy'],
                        handling: 'handling_verzelfstandigingen',// needs documentation
                        reload: true,
                        type: 'json',
                        url: '/json/verzelfstandigingen',
                        usepath: false
                    },
                    collect: {
                        from: 10,
                        title: 'Overig'
                    },
                    align: 'left', // needs documentation (alignment of navigation items)
                    currency: '',
                    back_title: "",
                    backlink: false,
                    charset: 'ISO-8859-1',
                    childlink: false,
                    childinfo: true,// needs documentation
                    divider: 1,
                    graph: 'pie',
                    legend: true,
                    legend_limit: true,
                    loader: true,
                    local: true,
                    multiplier: false,
                    negatives: false,
                    quicklink: false,
                    title: "Totaal fte",
                    select: [{
                        first: 'Ministerie...',// needs documentation
                        firstValue: false,// needs documentation
                        query: 'ministerie',// needs documentation
                        type: 'ministry'// needs documentation
                    }, {
                        query: 'jaar',
                        type: 'year'
                    }, {
                        first: 'zbo\'s en agentschappen',
                        firstValue: '',
                        query: 'type',
                        type: 'type'
                    }, {
                        query: 'soort',
                        type: 'sort',
                        size: 0.25,
                        currency: { // needs documentation
                            'fte': '',
                            'omzet': 'EUR'
                        },
                        title: { // needs documentation
                            'fte': 'Totaal fte',
                            'omzet': 'Totaal omzet'
                        },
                        multiplier: {
                            'fte': false,
                            'omzet': 1000
                        }
                    }],
                    shades: false,
                    target: 'chart_canvas',
                    use_identifiers_for_colors: false,
                    value: 'amount',
                    script_template_id: 'vzst', // needs documentation
                    slider: { // needs documentation
                        id: 'slider_vrzs',
                        type: 'double',
                        size: 0.7,
                        step: 1,
                        script: 'change_slider_vrzs',
                        reset: { 'soort': false } // needs documentation reload if query changes
                    }
                });

                readAPIFile(handle_json_data, {
                    target: 'chart_canvas_1',
                    api: {
                        structure: ['dummy'],
                        handling: 'handling_verzelfstandigingen_vergelijk',// needs documentation
                        url: '/json/verzelfstandigingen',
                        reload: true,
                        type: 'json',
                        usepath: false,
                        query: {
                            soort: 'omzet',
                            vergelijk: 1
                        }
                    },
                    charset: 'ISO-8859-1',
                    quicklink: false,
                    childlink: true,
                    legend: false,
                    backlink: false,
                    shades: false,
                    negatives: false,
                    graph: 'clustered-column',
                    bars: [],
                    bartotals: false,
                    bar_size: 0.4,
                    yaxis: true,
                    xaxis: true,
                    multiplier: false,
                    divider: 1,
                    nohighlight: true,
                    series: [], // array for manual selecting series
                    barcolors: true, // true, 'reversed' entire bar in 1 color
                    use_identifiers_for_colors: false,
                    select: [{
                        query: 'soort',
                        type: 'sort',
                        size: 0.17,
                        currency: { // needs documentation
                            fte: 'fte',
                            omzet: 'EUR'
                        },
                        title: { // needs documentation
                            'fte': 'Totaal fte',
                            'omzet': 'Totaal omzet'
                        },
                        multiplier: {
                            'fte': false,
                            'omzet': 1000
                        }
                    }],
                    selectbox: {
                        id: 'selector_vzst', // needs documentation (visual specific)
                        max: 3,
                        compare: 'series',
                        placeholder: 'Selecteer een zbo of agentschap...',
                        script: 'change_vzst_vergelijk' // needs documentation [script] (add/remove, chartId, optionId)
                    },
                    align: 'left', // needs documentation (alignment of navigation items)
                    script_template_id: 'vzst_vergelijk'
                });
            }
            // verzelfstandigingen
            // handle api data
            function handling_verzelfstandigingen(tmp_data, options) {
                // transfer data
                var other = 0;
                var children = [];
                var data = [];
                var min = 1000000000000;
                var max = 0;
                var index = 0;

                // set default year
                if (!options.api.query.jaar) {
                    for (var j in tmp_data.jaar) {
                        options.api.query.jaar = j;
                    }
                }

                // leave out zero data
                var td = [];
                for (var r in tmp_data.result) {
                    var val = tmp_data.result[r][options.api.query.soort][tmp_data.result[r]['jaar']] ? tmp_data.result[r][options.api.query.soort][tmp_data.result[r]['jaar']] : 0;
                    if (val > 0) {
                        td.push(tmp_data.result[r]);
                    }
                }
                tmp_data.result = td;

                for (var r in tmp_data.result) {
                    var val = tmp_data.result[r][options.api.query.soort][tmp_data.result[r]['jaar']] ? tmp_data.result[r][options.api.query.soort][tmp_data.result[r]['jaar']] : 0;
                    if (val > 0) {
                        if (min > val) {
                            min = val;
                        }
                        if (max < val) {
                            max = val;
                        }
                        if (Object.keys(tmp_data.result).length < options['collect'].from + 2 || (typeof (options['collect']) != 'undefined' && index < options['collect'].from)) {
                            var d = {
                                child_info: false,
                                children: false,
                                curyear: true,
                                identifier: tmp_data.result[r]['id'],
                                title: tmp_data.result[r]['titel'],
                                amount: val,
                                value: val,
                                fte: tmp_data.result[r]['fte'][tmp_data.result[r]['jaar']] ? tmp_data.result[r]['fte'][tmp_data.result[r]['jaar']] : 0,
                                omzet: tmp_data.result[r]['omzet'][tmp_data.result[r]['jaar']] ? tmp_data.result[r]['omzet'][tmp_data.result[r]['jaar']] : 0,
                                ministerie: ministerie_abbreviation[tmp_data.result[r]['ministerie'].toUpperCase()],
                                type: tmp_data.result[r]['type'],
                                beschrijving: tmp_data.result[r]['info']['beschrijving'] ? tmp_data.result[r]['info']['beschrijving'] : '',
                                website: tmp_data.result[r]['info']['website'],
                                website_class: tmp_data.result[r]['info']['website'] && tmp_data.result[r]['info']['website'] != '' ? 'show' : '',
                                link: "execute:set_manual_bars|chart_canvas_1|add|"+tmp_data.result[r]['titel']
                        };
                        data.push(d);
                    } else {
                            var d = {
                                identifier: tmp_data.result[r]['id'],
                                title: tmp_data.result[r]['titel'],
                                amount: val,
                                value: val,
                                fte: tmp_data.result[r]['fte'][tmp_data.result[r]['jaar']] ? tmp_data.result[r]['fte'][tmp_data.result[r]['jaar']] : 0,
                                omzet: tmp_data.result[r]['omzet'][tmp_data.result[r]['jaar']] ? tmp_data.result[r]['omzet'][tmp_data.result[r]['jaar']] : 0,
                                ministerie: ministerie_abbreviation[tmp_data.result[r]['ministerie'].toUpperCase()],
                                type: tmp_data.result[r]['type'],
                                beschrijving: tmp_data.result[r]['info']['beschrijving'] ? tmp_data.result[r]['info']['beschrijving'] : '',
                                website: tmp_data.result[r]['info']['website'],
                                website_class: tmp_data.result[r]['info']['website'] && tmp_data.result[r]['info']['website'] != '' ? 'show' : '',
                                link: "execute:set_manual_bars|chart_canvas_1|add|" + tmp_data.result[r]['titel']
                            };
                            children.push(d);
                            other += val;
                        }
                        index++;
                    }
                }
                if (other) {
                    d = {
                        amount: other,
                        child_info: true,
                        identifier: tmp_data.result[r]['id'],
                        title: options['collect'].title ? options['collect'].title : 'Overig',
                        value: other,
                        children: children,
                        no_card: true
                    };
                    data.push(d);
                }
                // prepare slider
                options.slider['start'] = options.api.query['min'] < min ? options.api.query['min'] : min;
                options.slider['end'] = options.api.query['max'] > max ? options.api.query['max'] : max;
                options.slider['min'] = tmp_data.min;
                options.slider['max'] = tmp_data.max;
                options.slider['round'] = true;
                options.slider['currency'] = options.api.query.soort == 'fte' ? 'fte' : 'EUR';

                return data;
            }
            // verzelfstandigingen
            // handle api data for compare visual
            function handling_verzelfstandigingen_vergelijk(tmp_data, options) {
                var selection = options.api.query.soort ? options.api.query.soort : 'fte';
                var data = [];
                for (var r in tmp_data.result) {
                    var d = {
                        title: tmp_data.result[r]['titel'],
                        identifier: tmp_data.result[r]['titel'],
                        type: tmp_data.result[r]['type'],
                        ministerie: ministerie_abbreviation[tmp_data.result[r]['ministerie'].toUpperCase()],
                        beschrijving: tmp_data.result[r]['info']['beschrijving'],
                        website: tmp_data.result[r]['info']['website'],
                        fte: tmp_data.result[r]['fte'][tmp_data.result[r]['jaar']],
                        omzet: tmp_data.result[r]['omzet'][tmp_data.result[r]['jaar']],
                        website_class: tmp_data.result[r]['info']['website'] && tmp_data.result[r]['info']['website'] != '' ? 'show' : ''
                    };
                    // add years
                    for (j in tmp_data.result[r][selection]) {
                        if (!options.bars[j]) { // to bars
                            options.bars.push(j);
                        }
                        d[j] = tmp_data.result[r][selection][j]; // to data
                    }
                    data.push(d);
                }
                return data;
            }
            // verzelfstandigingen
            // add/remove compare item
            function change_vzst_vergelijk(action, chartId, id) {
                if (action == 'add') {
                    var chartindex = -1;
                    for (var c in charts) {
                        if (charts[c].domId == chartId) {
                            chartindex = c;
                        }
                    }
                    if (chartindex > -1) {
                        for (var d in minfin_data[chartindex].data) {
                            if (minfin_data[chartindex].data[d].title == id) {
                                var target = document.getElementById("card_container");
                                var temp = document.getElementById("chart_card_template_vzst_vergelijk");
                                if (temp) {
                                    var node = mustache(temp.innerHTML, minfin_data[chartindex].data[d], false, chartindex);
                                    target.appendChild(node);
                                }
                            }
                        }
                    }
                    // scroll into view
                    $('#chart_canvas_1_navigation_top')[0].scrollIntoView();
                } else if (action == 'remove') {
                    $('.card').each(function () {
                        if ($(this).attr('value') == id) {
                            $(this).remove();
                        }
                    });
                }
            }
            // verzelfstandigingen
            // handle slider change
            function change_slider_vrzs(obj) {
                // reload with new min + max
                minfin_data[obj.options.chartId].options.api.query['min'] = obj.start;
                minfin_data[obj.options.chartId].options.api.query['max'] = obj.end;
                reloadnow(obj.options.chartId);
            }

            /*
            //
            // tijdlijn
            // add input after visual is ready
            //
            */

            function ready_tijdlijn(data, options) {
                // timeline exception
                if (options.preset) {
                    var ord = [];
                    for (var d in data) {
                        if (options.preset == 'identifier') {
                            var f = false;
                            for (var o in ord) {
                                if (ord[o].identifier == data[d].identifier) {
                                    f = true;
                                    ord[o].value += data[d][data[d].identifier];
                                }
                            }
                            if (!f) {
                                ord.push({
                                    identifier: data[d].identifier,
                                    value: data[d][data[d].identifier]
                                });
                            }
                        } else {
                            ord.push({
                                identifier: data[d].identifier,
                                value: data[d][options.preset]
                            });
                        }
                    }
                    ord.sort(function (a, b) {
                        var keyA = a.value,
                            keyB = b.value;
                        // Compare the 2 dates
                        if (keyA > keyB) return -1;
                        if (keyA < keyB) return 1;
                        return 0;
                    });
                    while (ord.length > 5) {
                        ord.pop();
                    }
                    var list = [];
                    for (d in ord) {
                        list.push(ord[d].identifier);
                    }
                    options.preset = false;
                    if (list.length) {
                        setTimeout(function (target) {
                            set_manual_bars(target, "add", list);
                        }, 1000, options.target);
                    }
                }
            }

            /*
            //
            // wie ontvingen
            // init search page
            //
            */

            function wie_ontvingen_start() {
                //https://rijksfinancien.acceptatie.indicia.nl/json/financiele_instrumenten/ontvangers
                readAPIFile(handle_json_data, {
                    anchor: 'wie-ontvingen-start-title',
                    api: {
                        default: [],
                        structure: ["identifier"],
                        trigger: 'wie-ontvingen',
                        url: '/json',
                        path: '/json/financiele_instrumenten', // for use without 'trigger' in api path need documentation
                        reload: true,
                        type: 'json',
                        query: {
                            jaar: '',
                            search: ''
                        },
                        handling: 'handling_wie_ontv_start',
                        usepath: true
                    },
                    collect: {
                        from: 10,
                        title: 'Overig'
                    },
                    select: [
                        {
                            query: 'jaar',
                            api: '/financiele_instrumenten/available_years',
                            type: 'year',
                            size: 0.25,
                            options: [],
                            script: 'wie_ontv_year'
                        }
                    ],
                    searchbox: { // needs documentation
                        id: 'selector_wie_ontv',
                        size: 0.5,
                        max: 20,
                        cut: 35,  // needs documentation
                        placeholder: 'Zoek...',
                        no_results: 'Er zijn geen resultaten gevonden...',
                        no_good_results: 'Niet gevonden wat je zocht? Gebruik zonodig een verbeterde zoekstring...',
                        max_results: 'Er zijn nog [x] resultaten. Gebruik zonodig een verbeterde zoekstring...',
                        script: 'search_wie_ontv'
                    },
                    graph: 'none',
                    align: 'left',
                    target: 'chart_canvas_wie_ontv_strt', // needed for select
                    script_template_id: 'wie_ontv_search',
                    charset: 'ISO-8859-1'
                });
            }
            function wie_ontv_year(e) {
                e[1].api.query['jaar'] = e[0];
                readAPIFile(handle_json_data, e[1], false, true);
            }
            // wie ontvingen
            // handle api data for search page
            function handling_wie_ontv_start(tmp_data, options) {
                var data = [];
                var aantal = 0;
                var mx = 0;
                for (var d in tmp_data) {
                    if (d == 'result') {
                        for (var dd in tmp_data[d]) {
                            aantal++;
                            mx = tmp_data[d][dd]['bedrag'];
                            data.push({
                                identifier: tmp_data[d][dd]['id'],
                                title: tmp_data[d][dd]['titel'],
                                value: mx
                            });
                        }
                    }
                }
                $('#chart_canvas_wie_ontv_strt_navigation').css('display', 'block');
                $('#navigation_select').css('display', 'block');
                $('#selector_wie_ontv.manual_selector').css('padding', 0);
                $('#selector_wie_ontv').css('display', 'block').css('border', 0);

                $('.searchheader_left').html(cap(minfin_api.path.identifier) + ' (' + tmp_data['total_results'] + ')');
                $('.searchheader_right').html('total');

                var obj = $('#selector_wie_ontv input');
                obj.attr('placeholder', options.searchbox.placeholder);
                if (obj.attr('hasevents') != 'ready') {
                    obj.bind('keyup', function (e) {
                        if (e.keyCode == 13) {
                            $(this).trigger('blur');
                        }
                    });
                    obj.bind('blur', { options: options }, function (e) {
                        if (e.data.options.api.query['search'] != $(this).val()) {
                            e.data.options.api.query['max'] = '';
                            e.data.options.api.query['search'] = $(this).val();
                            readAPIFile(handle_json_data, e.data.options);
                        }
                    });
                    obj.attr('hasevents', 'ready');
                }
                var res = $('#chart_canvas_wie_ontv_strt_results');
                res.html('');
                var i = 0;
                /* general
                var numofresults = 78 - options.searchbox.max;var posttxt = Object.keys(tmp_data['result']).length > 1 ? Object.keys(tmp_data['result']).length - 1 <= options.searchbox.cut ? '' : options.searchbox.max_results.replace('[x]', numofresults) : options.searchbox.no_results;
                */

                for (var d in data) {
                    //if (Object.keys(data).length - 1 <= options.searchbox.cut || i < options.searchbox.max) {
                    i++;
                    var row = $('<a class="searchrow">' + data[d]['title'] + '<div style="display:inline-block;float:right;color:black;">' + format_number(data[d]['value'], { currency: 'EUR' }) + '</div></a>');
                    row.bind('click', { id: data[d]['identifier'], options: options }, function (e) {
                        if (minfin_api.path.identifier == 'hoofdstukken') {
                            var referrer = 'hoofdstuk';
                        } else if (minfin_api.path.identifier == 'artikelen') {
                            var referrer = 'artikel';
                        } else if (minfin_api.path.identifier == 'regelingen') {
                            var referrer = 'regeling';
                        } else {
                            var referrer = 'ontvanger';
                        }
                        window.location.href = '/wie-ontvingen/visual?referrer=' + referrer + '&referrer_id' + '=' + e.data.id;
                        });
                        res.append(row);
                    //}
                }
                var numofresults = tmp_data['total_results'] - aantal;
                // still more results
                if(numofresults) {
                    var posttxt = Object.keys(tmp_data['result']).length < aantal ? '' : options.searchbox.max_results.replace('[x]', numofresults);
                    var row = $('<div class="postsearchrow">' + posttxt + '</div>');
                    var lnk = $('<a class="postsearchlink"href="javascript:void(0)">Toon meer resultaten</a>');
                    lnk.bind('click', {max: mx, options: options }, function(e) {
                        e.data.options.api.query['max'] = mx - 1;
                        readAPIFile(handle_json_data, e.data.options);
                    });
                    row.append(lnk);
                    res.append(row);
                } else if(!i && !numofresults) { // no results
                    res.append($('<div class="postsearchrow">' + options.searchbox.no_results + '</div>'));
                } else if(options.api.query['query'] != '') {
                    res.append($('<div class="postsearchrow">' + options.searchbox.no_good_results + '</div>'));
                } else {
                    res.append($('<div class="postsearchrow">--</div>'));
                }

                return data;
            }
            // wie ontvingen
            // handle slider change
            function change_slider_wie_ontv(obj) {
                // reload with new min + max
                //console.log(obj)
                minfin_data[obj.options.chartId].options.api.query['min'] = obj.start;
                minfin_data[obj.options.chartId].options.api.query['max'] = obj.end;
                reloadnow(obj.options.chartId);
            }
            // wie ontvingen
            // init visual
            function wie_ontvingen() {
                readAPIFile(handle_json_data, {
                    anchor: 'wie-ontvingen-title',
                    api: {
                        structure: ["dummy"],
                        trigger: 'financiele_instrumenten',
                        url: '/json',
                        reload: true,
                        type: 'json',
                        query: {
                            referrer: 'ontvanger',
                            referrer_id: '',
                            jaar: '',
                            type: 'ontvanger',
                            min: '',
                            max: '',
                            search: ''
                        },
                        handling: 'handling_wie_ontv',
                        usepath: true
                    },
                    collect: {
                        from: 14,
                        title: 'Overig'
                    },
                    select: [
                        {
                            query: 'jaar',
                            api: '/financiele_instrumenten/available_years',
                            type: 'year',
                            size: 0.25,
                            options: []
                        }, {
                            query: 'type',
                            type: 'type',
                            exclude: 'referrer',
                            options: ["hoofdstuk", "artikel", "regeling", "ontvanger"]
                        }, {
                            query: 'title',
                            type: 'title',
                            options:['Oi', '1', 'testing']
                        }
                    ],
                    input: [{
                        query: 'search',
                        type: 'title',
                        size: 0.4,
                        placeholder: 'Zoek titel...',
                    }],
                    // shades: false,
                    use_identifiers_for_colors: true,
                    // value: 'amount',
                    // script_template_id: 'vzst', // needs documentation
                    slider: { // needs documentation
                        id: 'slider_wie_ontv',
                        type: 'double',
                        size: 0.6,
                        step: 1,
                        script: 'change_slider_wie_ontv',
                        reset: { 'soort': false }, // needs documentation reload if query changes
                        min: 0,
                        max: 1000,
                        round: true,
                        currency: 'EUR'
                    },
                    align: 'left', // needs documentation (alignment of navigation items)
                    script_template_id: 'wie_ontv',
                    charset: 'ISO-8859-1',
                    target: 'chart_canvas_wie_ontv',
                    legend: true,
                    graph: 'sankey',
                    divider: 1000,
                    multiplier: 1
                });
            }
            // wie ontvingen
            // handle api data for visual
            function handling_wie_ontv(tmp_data, options) {
                var data = [];

                if(tmp_data['title'] == null) {
                    tmp_data['title'] = '';
                }
                if(tmp_data['max'] == null) {
                    tmp_data['max'] = 1;
                }

                // foreword or backward visual
                var testarray = ['', 'hoofdstuk', 'artikel', 'regeling', 'ontvanger'];
                options.targetfrom = testarray.indexOf(tmp_data.referrer) < testarray.indexOf(options.api.query.type) ? true : false;

                // make titles
                var m = options.api.query.type;
                if (m == 'artikel' || m == 'regeling') {
                    m += 'en';
                } else if (m == 'hoofdstuk') {
                    m += 'ken';
                } else {
                    m += 's';
                }
                if (options.targetfrom) {
                    $('.wie-ontvingen-title').html('Van ' + tmp_data.referrer + ' ' + tmp_data.title + ' naar ' + m);
                    options.legend_title = 'Totaal ontvangen van ' + tmp_data.referrer + ' ' + tmp_data.title;
                } else {
                    $('.wie-ontvingen-title').html('Van ' + m + ' naar ' + tmp_data.referrer + ' ' + tmp_data.title);
                    options.legend_title = 'Totaal ontvangen van ' + m;
                }

                // set first record
                var data = [];
                var obj = {
                    title: tmp_data['title'],
                    nodeColor: "#01689B",
                    noindex: true
                };
                if (options.targetfrom) {
                    obj['to'] = tmp_data['title'];
                } else {
                    obj['from'] = tmp_data['title'];
                }
                data.push(obj);

                // add rest of data
                var index = 0;
                var totaal = typeof (tmp_data['total']) != 'undefined' ? tmp_data['total'] : tmp_data['max'];
                var totaalshown = 0;
                var value = 0;
                for (var r in tmp_data['result']) {
                    if (Object.keys(tmp_data['result']).length < options['collect'].from + 3 || (typeof (options['collect']) != 'undefined' && index < options['collect'].from)) {
                        value = tmp_data['result'][r]['amount'];
                        totaalshown += value;
                        obj = {
                            link: 'execute:reload_wie_ontv|' + options.api.query.type + '|' + tmp_data['result'][r]['id'],
                            value: tmp_data['result'][r]['amount'],
                            title: tmp_data['result'][r]['title'], // + ' ' + (index + 1),
                            type: options.api.query.type
                        };
                        if (options.targetfrom) {
                            obj['pre'] = 'Door naar';
                            obj['from'] = tmp_data['referrer_id'];
                            obj['to'] = tmp_data['result'][r]['title'] + ' ' + (index + 1);
                            obj['popup_title'] = 'Van ' + tmp_data.referrer + ' ' + tmp_data.title + ' naar ' + options.api.query.type + ' ' + tmp_data['result'][r]['title'];
                        } else {
                            obj['pre'] = 'Terug naar';
                            obj['to'] = tmp_data['referrer_id'];
                            obj['from'] = tmp_data['result'][r]['title'] + ' ' + (index + 1);
                            obj['popup_title'] = 'Van ' + options.api.query.type + ' ' + tmp_data['result'][r]['title'] + ' naar ' + tmp_data.referrer + ' ' + tmp_data.title;
                        }
                        data.push(obj);
                        index++;
                    }
                }

                // set arrow id only a single result
                if(index == 1 || index == 2) {
                    $('#' + options.target).addClass('sankey_background');
                } else {
                    $('#' + options.target).removeClass('sankey_background');
                }

                // prepare slider
                var min = tmp_data['min'];
                var max = tmp_data['max'];
                options.slider['start'] = options.api.query['min'] != '' && options.api.query['min'] > min ? options.api.query['min'] : min;
                options.slider['end'] = options.api.query['max'] && options.api.query['max'] < max ? options.api.query['max'] : max;
                options.slider['min'] = tmp_data.min;
                options.slider['max'] = tmp_data.max;

                // set 'other' value for manual legend item
                options['legend'] = []
                if (tmp_data.total_results > index) {
                    options['legend'].push(
                        {
                            //value: (totaal - totaalshown),
                            title: 'Toon meer ' + m,
                            type: 'other',
                            link: 'execute:set_min_wie_ontv|' + (value)
                        }
                    );
                    options['legend'].push(
                        {
                            title: tmp_data['title'] + ' ',
                            type: "fill", // fill, pattern, line, bullet, dash
                            color: "blue",
                            value: totaalshown
                        }
                    );
                }

                options.total = totaal;

                // back link and title
                var r = tmp_data['referrer'];
                if (r == 'artikel' || r == 'regeling') {
                    r += 'en';
                } else if (r == 'hoofdstuk') {
                    r += 'ken';
                } else {
                    r += 's';
                }
                $('#navigation_links a').html(cap(r) + ' zoeken');
                $('#navigation_links').bind('click', function () {
                    document.location.href = '/wie-ontvingen/' + r;
                });

                return data;
            }
            function reload_wie_ontv(args) {
                minfin_data[args[2]].options.api.query['type'] = args[0] == 'ontvanger' ? 'hoofdstuk' : 'ontvanger';
                minfin_data[args[2]].options.api.query['referrer'] = args[0];
                minfin_data[args[2]].options.api.query['referrer_id'] = args[1];
                minfin_data[args[2]].options.api.query['min'] = '';
                minfin_data[args[2]].options.api.query['max'] = '';
                //minfin_data[args[1]].options.api.query['min'] = minfin_data[args[1]].options.slider['min'];
                //minfin_data[args[1]].options.api.query['max'] = args[0];
                reloadnow(args[2]);
            }
            function set_min_wie_ontv(args) {
                //console.log(minfin_data[args[1]].options)
                minfin_data[args[1]].options.api.query['min'] = minfin_data[args[1]].options.slider['min'];
                minfin_data[args[1]].options.api.query['max'] = args[0] - 1;
                reloadnow(args[1]);
            }

        // END INIT

        }
    };
}(jQuery, Drupal));
