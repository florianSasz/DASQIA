<?php
/**
 * class to draw a diagram on the project page
 */
require_once "../components/SVGGraph/autoloader.php";

class DrawGraph {
    private string $xLabel;
    private string $yLabel;
    private int $axisFontSize;
    private int $labelFontSize;
    private int $vGridDivision;

    private int $width;
    private int $height;
    private array $colors;
    private array $settings;

    function __construct(string $xLabel, string $yLabel, int $axisFontSize, int $labelFontSize, int $vGridDivision, string $graphColor) {
        $this->xLabel = $xLabel;
        $this->yLabel = $yLabel;
        $this->axisFontSize = $axisFontSize;
        $this->labelFontSize = $labelFontSize;
        $this->vGridDivision = $vGridDivision;
        $this->graphColor = $graphColor;
        $this->setupGraphs();
    }

    public function drawAllGraphs(array $graphs) {
        $output = $this->openGraphSpace();
        
        foreach ($graphs as $i=>$graph) {
            if ($i % 2 == 0) {
                $output .= "<tr>";
            }
            $output .=
            "<td>
                <b><p class='blackSubHeadline sameLine' id='graphTitle_" . $i . "'>" . $graph["title"] . "</p></b>";

                if ($i > 1) { // for each RQ saturation 
                    $output .= "<p class='greyText blackSubHeadline sameLine'>&nbsp;- code saturation</p>";
                }

            $output .=
                "<div class='diagramm'>";

                $graphHTML = $this->drawGraph($graph["data"]);
                $output .= $graphHTML[0]; // graph itself
                $output .= $graphHTML[1]; // js for graph interaction
                $output .= $graph["csv"];
            
            $output .= // class "downloadButton" not used in css but in graph.js 
                    "<div class='downloadButtonsContainer'>
                        <button id='downloadSVG_" . $i . "' class='button downloadButton'>download .svg</button>
                        <button id='downloadPNG_" . $i . "' class='button downloadButton'>download .png</button>
                        <button id='downloadCSV_" . $i . "' class='button downloadButton'>download .csv</button>
                    </div>
                </div>
            </td>";

            if ($i % 2 == 1) {
                $output .= "</tr>";
            }
        }

        $output .= $this->closeGraphSpace();
        return $output;
    }

    private function openGraphSpace() {
        return 
        "<table id='graphSpace' class='tableWidth'>
            <tablebody>";
    }

    private function closeGraphSpace() {
        return 
            "</tablebody>
        </table>";
    }

    private function setupGraphs() {
        $this->width = 550;
        $this->height = 440;
        $this->colors = array($this->graphColor); // default: rgb(87, 115, 255)
        $this->settings = array(
            "back_colour" => "rgb(255, 255, 255)",
            "auto_fit" => false,
            "show_grid_v" => false,
            "stroke_width" => 0,
            "label_v" => $this->yLabel,
            "label_h" => $this->xLabel,
            "structure_data" => true,
            "structure" => array("key" => "documentIndex", "value" => "numberCodes"),
            "force_assoc" => true,
            "id_prefix" => "svg_",
            "axis_font_size" => $this->axisFontSize,
            "label_font_size" => $this->labelFontSize,
            "axis_font" => "arial",
        );
        
        if ($this->vGridDivision > 0) {
            $this->settings["grid_division_v"] = $this->vGridDivision;
        }
    }

    private function drawGraph(array $data) {
        // https://www.goat1000.com/svggraph.php
        // $data = ["documentIndex" => , "numberCodes" => ]
        $graph = new Goat1000\SVGGraph\SVGGraph($this->width, $this->height, $this->settings);
        $graph->colours($this->colors);
        $graph->values($data);
        return array($graph->fetch("BarGraph"), $graph->fetchJavascript());
    }
}
?>