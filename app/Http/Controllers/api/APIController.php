<?php

namespace App\Http\Controllers\api;
use Response;
use Illuminate\Http\Request;
use Config;
use App\Http\Controllers\Controller;
use App\User;
use Auth;
/**
 * Class APIController
 *
 * @package App\Http\Controllers\api
 *
 * @SWG\Swagger(
 *     basePath="/api",
 *     host="http://localhost/taskbench",
 *     schemes={"http"},
 *     @SWG\Info(
 *         version="1.0",
 *         title="Task Bench API", *
 *     ), *
 * )
 */
class APIController extends Controller 
{

     public function __construct()
    {
        $this->successStatus    = Config::get('custom.http_codes.success');
        $this->validationError  = Config::get('custom.http_codes.validation');
        $this->otherError       = Config::get('custom.http_codes.other');
        $this->durationtime     = new DurationTime();
        $this->emailcontent     = new EmailContent();
        $this->user             = new User();
        $this->taskAttachment   = new TaskAttachment();
        $this->Countries        = new Country();
        $this->language         = new Language();
        $this->timezone         = new TimeZone();
        
    }
    
    public function getTimeZone()
    {
        return "Africa/Abidjan";
    }
    public function SendResponse($message,$data=null,$status=null,$code=null,$httpcode=null) 
    {
       
         $status    = $status=='' ? 'success' : $status;
         $code      = $code=='' ? '200' : $code;
         $httpcode  = $httpcode=='' ? '200' : $httpcode;
         return response()->json([
                        'status' => $status,
                        'code' => $code,
                        'message' => $message,
                        'data'=>$data,
                            ],$httpcode);
    }
    
    public function GeneralSetup()
    {
        $generalArray = array();
        $generalArray['tag_person']     = Config::get('custom.tag_person');
        $generalArray['time_duration']  = $this->durationtime->select('id','duration')->get();
        $generalArray['status']         = Config::get('custom.taskStatus');
        $generalArray['category_group'] = Config::get('custom.category_group');
        $generalArray['country']        = Config::get('custom.country');
        $generalArray['countrycode']    = Config::get('custom.country_code');
        $generalArray['timezone']       = Config::get('custom.time_zone');
        $generalArray['language']       = Config::get('custom.languages');
        $generalArray['task_reminders'] = Config::get('custom.task_reminders');
        $generalArray['task_priority']  = Config::get('custom.task_priority');
        $generalArray['skills']         = $this->ConverToJsonArray(Config::get('custom.skills'));
         return $this->SendResponse('General Setup',$generalArray);
    }
    
    public function CheckISNull($param=NULL)
    {
        $return  = $param=='' ? '' : $param;
        return  $return;
    }
    
     public function GetFilePath($folderPath,$fileName)
    { 
         $getTaskID  = $this->taskAttachment->where('filename', 'like',$fileName)->first();
         $taskId = $getTaskID->task_id;
        $fullpath="app/public/{$folderPath}/{$taskId}/{$fileName}";
         return response()->download(storage_path($fullpath), null, [], null);
    }
    
    public function rand_string($length) {

        $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
        return substr(str_shuffle($chars),0,$length);

    }
    
    
    public function CountriesDetail()
    { 
       $generalArray = array();
      
        $generalArray['country']        = $this->Countries->select('id','name','full_name','capital','citizenship','currency','currency_code','currency_sub_unit','currency_symbol','country_code','calling_code','region_code')->orderby('name','asc')->get();
        $generalArray['timezone']       = $this->timezone->select('text as name','utc','id')->get();
        $generalArray['language']       = $this->language->select('id','alpha3','alpha2','english')->get();
        return $this->SendResponse('General Setup',$generalArray);
    }

    public function CountryDetailByID($countryId){
        return $this->Countries->getDetailsById($countryId);
    }
    public function ConverToJsonArray($arrayValue)
    {
        $jsonArray=[];
        if(isset($arrayValue))
        {
            foreach($arrayValue as $key=>$value)
            {
                $jsonArray[] = array('id'=>$key,'value'=>$value);
            }
        }
        return $jsonArray;
    }

}