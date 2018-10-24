<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use PhpOffice\PhpSpreadsheet\IOFactory;
use App\Models\LinepayStore;
use App\Models\Ticket\Supplier;

class UpdateLinePayMapStores extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'data:uploadLinePayMapStores {api_key} {source=database} {filename?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'upload linpay map store from excel or database';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $source = $this->argument('source');
        if ($source == 'database') {
            $this->importByDatabase();
        } else if ($source == 'excel') {
            $this->importByExcel();
        }
    }
    
    /*
     *  excel 欄位順序: 名稱, 類別, 電話, 營業時間, 地址, 經度(選填), 緯度(選填)
     */
    protected function importByExcel() 
    {
        $api_key = $this->argument('api_key');
        $inputFileName = storage_path('app/LINEPayMap/' . $this->argument('filename'));
        $reader = IOFactory::createReader(IOFactory::identify($inputFileName));
        $spreadsheet = $reader->load($inputFileName);
        $sheetData = $spreadsheet->getActiveSheet()->toArray(null, true, true, true);
        foreach ($sheetData as $key => $val) {
            if ($key <= 1 ) continue;
            $insert_data['name'] = $val['A'];
            $insert_data['type'] = $val['B'];
            $insert_data['phone'] = $val['C'];
            $insert_data['business_hour'] = $val['D'];
            $insert_data['address'] = $val['E'];
            $insert_data['latitude'] = (float)$val['F'];
            $insert_data['longitude'] = (float)$val['G'];
            if (empty($insert_data['latitude'])) {
                $location = $this->getLocation($insert_data['address'] ,$api_key);
                $insert_data['latitude'] = $location['latitude'];
                $insert_data['longitude'] = $location['longitude'];
            }
            LinepayStore::create($insert_data);
        }
    }
    
    protected function importByDatabase()
    {
        $api_key = $this->argument('api_key');
        $suppliers = (new Supplier)
                        ->whereNotNull('supplier_address')
                        ->where([
                                    'supplier_import_linepay_store' => 0,
                                    'supplier_status' => 1,
                                    'deleted_at' => 0
                                ])
                        ->where('supplier_name', 'NOT LIKE', '%(test)')
                        ->where('supplier_name', 'NOT LIKE', '%(已到期)')
                        ->get();
        foreach ($suppliers as $key => $val) {
            $insert_data['name'] = empty($val['supplier_name']) ? '' : $val['supplier_name'];
            $insert_data['type'] = '';
            $insert_data['phone'] = empty($val['supplier_tel']) ? '' : $val['supplier_tel'];
            $insert_data['business_hour'] = '';
            $insert_data['address'] = (empty($val['supplier_county']) || empty($val['supplier_district']) || empty($val['supplier_address']))
                                            ? ''
                                            : ($val['supplier_county'] . $val['supplier_district'] . $val['supplier_address']);
            if (empty($insert_data['address'])) continue;
            $location = $this->getLocation($insert_data['address'] ,$api_key);
            $insert_data['latitude'] = $location['latitude'];
            $insert_data['longitude'] = $location['longitude'];
            LinepayStore::create($insert_data);
            Supplier::where('supplier_id', $val['supplier_id'])->update(['supplier_import_linepay_store' => 1]);
        }
    }

            
    protected function getLocation($address, $api_key)
    {
        sleep(1); //不能讀太快，每筆要間隔一秒
        set_time_limit(10);
        $addr_str_encode = urlencode($address);
        $url = "https://maps.googleapis.com/maps/api/geocode/json"
            ."?language=zh-TW&region=tw&address=".$addr_str_encode."&key=".$api_key;
        
        $geo = file_get_contents($url);
        $geo = json_decode($geo,true);
        $geo_status = $geo['status'];
        echo "$address $geo_status\n";
        if($geo_status=="OVER_QUERY_LIMIT"){ die("OVER_QUERY_LIMIT"); }
        if($geo_status!="OK") return false;

        $geo_address = $geo['results'][0]['formatted_address'];
        $num_components = count($geo['results'][0]['address_components']);
        //郵遞區號、經緯度
        $geo_zip = $geo['results'][0]['address_components'][$num_components-1]['long_name'];
        $geo_lat = $geo['results'][0]['geometry']['location']['lat'];
        $geo_lng = $geo['results'][0]['geometry']['location']['lng'];
        $geo_location_type = $geo['results'][0]['geometry']['location_type'];
        /*
        location_type 會儲存指定位置的其他相關資料，目前支援的值如下：

        "ROOFTOP" 會指出傳回的結果是精準的地理編碼，因為結果中位置資訊的精確範圍已縮小至街道地址。
        "RANGE_INTERPOLATED" 表示傳回的結果反映的是插入在兩個精確定點之間 (例如十字路口) 的約略位置 (通常會在街道上)。如果 Geocoder 無法取得街道地址的精確定點地理編碼，就會傳回插入的結果。
        "GEOMETRIC_CENTER" 表示傳回的結果是結果的幾何中心，包括折線 (例如街道) 和多邊形 (區域)。
        "APPROXIMATE" 表示傳回的結果是約略位置。	
        */
            //if($geo_location_type!="ROOFTOP") continue;
        return array('latitude' => $geo_lat, 'longitude' => $geo_lng);

    }
}
