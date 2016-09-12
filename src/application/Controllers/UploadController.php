<?php
//Set NameSpace
namespace App\Controllers;

//Use
use App\Controller as Controller;
use App\Models\Image as Image;

//UploadController Class
class UploadController extends Controller
{

    public function index($request, $response, $args)
    {
        //Set the array for images names
        $uploadedFilesNames = [];

        //Get webiste settings
        $settings = $this->db->table('settings')->where('id', '=', '1')->first();

        //Get extensions
        $extensions = explode(',', $settings->file_extensions);

        //Check if the image exixts
    	if (!isset($_FILES['files']['name'][0])) {
    		return json_encode(['error' => 'No file was uploaded.']);
    	}

        //ReArray the javascript post 
    	$files = $this->reArrayFile($_FILES['files']);

    	foreach ($files as $file) {
            
            //Set images rules
    		$name = $this->generate();
    		$this->upload->setFile($file);
    		$this->upload->setFileName($name);
    		$this->upload->setAllowedExtensions($extensions);
    		$this->upload->setMaximumFileSize($settings->max_file_size);
    		$this->upload->setUploadDir(__DIR__ . '/../../../storage/');

            //Check if image is uploaded
    		if (!$this->upload->isUploaded()) {
    			return json_encode(['error' => $this->upload->getFirstError()]);
    		}

            //Get image Owner: username and userid
            if($this->session->get('uid')){
                $id = $this->session->get('uid');
                $user = $this->db->table('users')->where('id', '=', $id)->first();
                $owner = $user->username;
            }elseif($this->session->get('admin')) {
                $id = $this->session->get('admin');
                $user = $this->db->table('users')->where('id', '=', $id)->first();
                $owner = $user->username;
            }else{
                $id = '0';
                $owner = 'Anonim User';
            }
            
            //Get Image link to file
            $link = $request->getUri()->getBaseUrl() . "/storage/" . $this->upload->getNewFileName();

            //Get Images size
            $size = $this->upload->getFileSize();

            //Insert the image data
            Image::create(['oid' => $id ,'owner' => $owner, 'link' => $link, 'name' => $name, 'size' => $size, 'deleted' => '0']);

            //Add names to array
            $uploadedFilesNames[] = [$name];
    		
    	}

        //ReArray the file names
        $count = count($uploadedFilesNames);
        for ($x = 0; $x < $count; $x++){
            foreach ($uploadedFilesNames as $value) {
                $newData[$x] = $uploadedFilesNames[$x][0]; 
            }
        }

        //Implode NewData array
        $implodeNames = implode($newData, ',');
        $url = $request->getUri()->getBaseUrl() . $this->router->pathFor('getViewImage') . '/' . $implodeNames;

        //Return Javascript response    
    	return json_encode(['url' => $url, 'success' => true ]);
    }
    
    protected function reArrayFile($data = [])
    {
    	$newData = [];
    	$count = count($data['name']);
    	for ($x = 0; $x < $count; $x++) {
    		foreach ($data as $name => $value) {
    			$newData[$x][$name] = $value[$x];
    		}
    	}
    	return $newData;
    }

    protected function generate($length = 5) {
        $char = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charLength = strlen($char);
        $rString = '';
        for ($i = 0; $i < $length; $i++) {
            $rString .= $char[rand(0, $charLength - 1)];
        }
        return $rString;
    }
}
