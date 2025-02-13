<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Category;
use App\Models\ProductCategories;
use Illuminate\Support\Facades\File;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $Products = Product::paginate(10);

        return view('backend.product.index', compact('Products'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {   
        $categorys = Category::get();
        return view('backend.product.create',compact('categorys'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {   

        //dd($request->all());
        $request->validate([
            'title' => 'required',
            'price' => 'required',
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:5048',                
        ]);
        $Product= new Product;
        $Product->title         = $request->title;        
        $Product->price         = $request->price;        
        $Product->description   = $request->description;
        $Product->status        = $request->status;


        if ($request->hasFile('image')) {            
            $image1 = $request->file('image');
            $name1 = time().'.'.$image1->getClientOriginalExtension();
            $destinationPath = public_path('/images/product');
            $image1->move($destinationPath, $name1);
            $Product->fimage=$name1;
        }

       

        $Product->save();
        if(!empty($request->category_id)){
            $product_categories = array();
            foreach ($request->category_id as $key => $value) {
                $product_categories[$key]['categories_id']=$value;
                $product_categories[$key]['product_id']=$Product->id;
            }

            ProductCategories::insert($product_categories);
        }
        return redirect()->route('product.index')->with('success','roduct created successfully.');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(Product $Product)
    {   

        $categorys = Category::get();
        //echo"<pre>";
        //print_r($categorys);die("SDFaas");
        $productCategories =$Product->categories;
        $prod_cat=array();
        foreach ($productCategories as $key => $value) {
           $prod_cat[]=$value->categories_id;
        }

       // echo"<pre>";
       // print_r($prod_cat);die("SDFaas");
        return view('backend.product.edit',compact('Product','categorys','prod_cat'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Product $Product)
    {   
       $request->validate([
           'title' => 'required',
           'price' => 'required',
       ]);

      

       $fimage = $Product->fimage;
       if ($request->hasFile('image')) {
           
           $image1 = $request->file('image');
           $name1 = time().'.'.$image1->getClientOriginalExtension();
           $destinationPath = public_path('/images/product');
           $image1->move($destinationPath, $name1);
           $fimage=$name1;
       }

       $Product->update([
            'title' =>  $request->title,
            'price' =>  $request->price,
            'description' =>  $request->description,
            'status' =>  $request->status,
            'slug' =>  Product::createSlug($request->title),
            'fimage' =>$fimage
       ]);

       if(!empty($request->category_id)){
            ProductCategories::where('product_id',$Product->id)->delete();
           $product_categories = array();
           foreach ($request->category_id as $key => $value) {
               $product_categories[$key]['categories_id']=$value;
               $product_categories[$key]['product_id']=$Product->id;
           }

           ProductCategories::insert($product_categories);
       }
       
       return redirect()->route('product.index')->with('success','Category updated successfully');

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    
    public function destroy(Product $Product)
    {
        $Product->delete();
        return redirect()->route('product.index')->with('success','Product deleted successfully');
    }
   
}
