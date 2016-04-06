<?php
set_time_limit(-1);
class CradResizer {
    public  $image,
            $ratioW,
            $ratioH,
            $width,
            $height,
            $mime,
            $name,
            $folder = "cards/",
            $folders = [
                    'English'   ,
                    'French'    ,
                    'German'    ,
                    'Portuguese',
                    'Russian'   ,
                    'Spanish'   ,
            ],
            $sizes = [
                'large' => 768,
                'medium' => 512,
                'small' => 256
                //,'original' => 1024,
            ];
    
    
    function __construct(){
            // Make all Folders cards/language/size/file.png
            if(!file_exists($this->folder)){
                mkdir($this->folder, 0777);
            }

            foreach($this->folders as $folder){
                if(!file_exists($this->folder.'/'.$folder)){
                    mkdir($this->folder.'/'.$folder, 0777);
                }
                foreach($this->sizes as $name => $size){
                    if(!file_exists($this->folder.'/'.$folder.'/'.$name)){
                        mkdir($this->folder.'/'.$folder.'/'.$name, 0777);
                    }
                }

            }
 
    }
    
    public function newFile($file){
        list($width, $height) = getimagesize($file);
        $this->image = $file;
        $this->width = $width;
        $this->height = $height;
        $this->mime = getimagesize($file)['mime'];
        $this->ratioW = $height / $width;
        $this->ratioH = $width / $height;
        $this->name = basename($file);
    }
    
    public function resizeWidth($w, $file, $folder){
        $this->newFile($file);
        $h = $w * $this->ratioW;
        return $this->resize($w, $h, $folder);
    }
    
    public function resizeHeight($h, $file, $folder){
        $this->newFile($file);
        $w = $h * $this->ratioH;
        return $this->resize($w, $h, $folder);
    }
    
    public function resize($w, $h, $folder){
        $image = imagecreatetruecolor($w/1.7, $h);
        imagealphablending($image, true);
        imagesavealpha($image, true);
        
        $folder = $this->folder.$folder."/".$this->name;
        
        if($this->mime === 'image/png'){
            imagefill($image,0,0,0x7fff0000);
            $source = imagecreatefrompng($this->image);
        } elseif($this->mime === 'image/jpeg'){
            $source = imagecreatefromjpeg($this->image);
            
        } else {
            die('Wrong Image type, only jpeg or png');
        }
       
        // Resize
        imagecopyresized($image, $source, -$w * .20, 0, 0, 0, $w, $h, $this->width, $this->height);

        // Output
        if($this->mime === 'image/png'){
            imagepng($image, $folder);
            imagedestroy($image);
        } elseif($this->mime === 'image/jpeg'){
            imagejpeg($image, $folder);
            imagedestroy($image);
        }
        
        return  $folder;
    }
    
    public function resizeFolder($folder){
        $source = scandir($folder);
        unset($source[0]);
        unset($source[1]);
        foreach($source as $langFolder){
            if(is_dir($folder.'/'.$langFolder)){
                // Scan Language Folder
                $langSource = scandir($folder.'/'.$langFolder);
                unset($langSource[0]);
                unset($langSource[1]);
                
                // Loop Language Folder
                foreach($langSource as $key => $file){
                        // resize image to all sizes
                        foreach($this->sizes as $name => $value){
                            $this->resizeWidth($value, $folder.'/'.$langFolder.'/'.$file, $langFolder.'/'.$name);
                        }

                }
            }
        }
        
        echo 'Done.';
    }
    
}

$cr = new CradResizer();
$cr->resizeFolder("CardExport");