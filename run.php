<?php
    class groupController {
        private $scenesArray;
        private $group = "";
        private $hueIP = "";
        private $hueUsername = "";

        function __construct(){
            $this->array = array();
        }

        function getRequest($url) {
            $ch = curl_init();
            $headers = array();
            if (strpos($url, 'clip/v2') !== false) {
                $headers[] = 'hue-application-key: '.$this->hueUsername.'';
                curl_setopt($ch, CURLOPT_URL, 'https://'.$this->hueIP.''.$url.'');
                curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
            } else {
                $headers[] = '';
                curl_setopt($ch, CURLOPT_URL, 'http://'.$this->hueIP.'/api/'.$this->hueUsername.''.$url.'');
            }
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_TIMEOUT, 5);
            $result = curl_exec($ch);
            if (curl_errno($ch)) {
                echo 'Error:' . curl_error($ch);
                throw new Exception("Error thrown");
            }

            curl_close($ch);
            return $result;
        }

        function putRequest($url, $field) {
            $ch = curl_init();
            $headers = array();
            if (strpos($url, 'clip/v2') !== false) {
                $headers[] = 'hue-application-key: '.$this->hueUsername.'';
                curl_setopt($ch, CURLOPT_URL, 'https://'.$this->hueIP.''.$url.'');
                curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
            } else {
                $headers[] = '';
                curl_setopt($ch, CURLOPT_URL, 'http://'.$this->hueIP.'/api/'.$this->hueUsername.''.$url.'');
            }
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_TIMEOUT, 5);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
            curl_setopt($ch, CURLOPT_POSTFIELDS, $field);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            
            $result = curl_exec($ch);    
            if (strpos($result, 'errors":[]') !== false) $result .= "success";
            echo "putRequest: " . $result;
            if (curl_errno($ch) || strpos($result, 'success') == false) {
                echo 'Error:' . curl_error($ch);
                echo $result;
                throw new Exception("Error thrown");
            }

            curl_close($ch);
            return $result;
        }

        function fetchScenes() {
            $scenes = json_decode($this->getRequest("/scenes"), true);
            
            $this->scenesArray[] = "colorloop";

            foreach($scenes as $key => $scene) {
                if(isset($scene["group"]))
                    if ($scene["group"] == "8") 
                        if($scene["name"] != "Bright" && $scene["name"] != "Dimmed" && $scene["name"] != "Nightlight" && $scene["name"] != "Scene previous ") {
                            $this->scenesArray[] = $key;
                        }   
            }

            print_r($this->scenesArray);
        }

        function getRandomScene() {
            return $this->scenesArray[array_rand($this->scenesArray)];
        }

        function setScene($sceneID) {
            if ($sceneID == "colorloop") {
                echo "Setting Scene: " . $sceneID;
                $this->putRequest("/groups/$this->group/action", '{"effect":"colorloop", "on": true}');
            } else {
                echo "Scene is a dynamic. Searching for dynamic scene. ";
                $notFound = false;
                $scenesDynamic = json_decode($this->getRequest("/clip/v2/resource/scene"), true);
                foreach($scenesDynamic as $key => $scene) {
                    foreach($scene as $obj) {
                        if ($obj["id_v1"] == '/scenes/'.$sceneID.'') {
                            $notFound = true;
                            $sceneIDNew = $obj["id"];
                            echo "Setting dyanmic scene: " . $sceneIDNew;
                            $this->putRequest("/clip/v2/resource/scene/$sceneIDNew", '{"recall": {"action": "dynamic_palette"}}');
                            break;
                        }                       
                    }
                }

                if (!$notFound) {
                    echo 'No dynamic scene found (possible error, or scene is a storageScene scene (hue labs). ';
                    echo "Setting original scene: " . $sceneID;
                    $this->putRequest("/groups/$this->group/action", '{"scene": "'.$sceneID.'"}');
                }
                $this->putRequest("/groups/$this->group/action", '{"bri": 179}');
            }
        }

    }

    $hueControl = new groupController();

    $hueControl->fetchScenes();
    $hueControl->setScene($hueControl->getRandomScene());

?>
