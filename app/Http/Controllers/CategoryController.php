<?php

namespace App\Http\Controllers;

use App\Models\Category;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
class CategoryController extends Controller
{


    // public function __construct()
    // {
    //     $this->authorizeResource(Category::class , 'category');
    // }


    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
        $data = Category::paginate(10);
        return new Response(['status'=>true ,'message'=>'Success' , 'data'=>$data] , Response::HTTP_OK );
    } // new HttpResponse -->  انشئ اوبجكت من كلاس ريسبونس
     // بعدها رح ينفذ دالة الكونستركتور اللي هيا اول ما ينفذ عند انشاء اوبجكت من كلاس


    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // return response(['category'=>$request->all()]);
            // dd($request->all());
        $validator = Validator($request->all(),
        [
            'name'=>'required|string|min:3',
            'email'=>'required|string|email',
            'age'=>'string',

            'image' => 'nullable|image|mimes:jpg,png|max:1024',


        ]);
        if (!$validator->fails()) {
            // هاي مابتشتغل الا لو سمحتلها عن طريق المودل انه يعمللها فيل

            // طريقة جديدة للحفظ ,,, باستخدمها في حال لم يكن هناك عمليات قبل التخزين مثلا؟
           // زي اضافة قيمة مضافة للسعر او ضريبة
           // يعني باستخدمها لما بدي اخزن مباشرة





        //    $category = Category::create($request->all());
           $category = new Category();


           $category->name = $request->input('name');
           $category->email = $request->input('email');
           $category->age = $request->input('age');

           if($request->hasFile('image'))
           {
               $categoryImage = $request->file('image'); // جبنا الملف الخاص بملف الصورة
               $imageName = time() . '_image_' . $category->name . '.' . $categoryImage->getClientOriginalExtension(); //اسم لملف الصورة
               $categoryImage->storePubliclyAs('categories' , $imageName , ['disk' =>'public']); // نقلنا الصورة داخل مجلد اسمه ادمنز داخل الستورج
               $category->image = 'categories/' . $imageName; // عشان يخزنلي الصورة في الداتا بيز بنعطيه بيانات الصورة وين موجودة واسمها
           }
           $saved = $category->save(); 

            return new Response(['message'=>$saved ]);
        }else {
            return new Response(['message'=>$validator->getMessageBag()->first()]);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Category $category)
    {
        // $this->authorize('view' , $category);// حطيت متغير كاتيجوري لانه انا بدي اتعامل مع العنصر اللي تم عليه الحدث
        //  لحاله بيعمل فايند اور فيل اليكونت وبجيب العنصر لحاله
        // return response(['category'=>$category]); // كيف اخذ البيانات من هنا بالتفصيل ؟؟؟
        $data = Category::simplePaginate();
        return new Response(['status'=>true , 'message'=>'success' , 'data'=>$data]);

    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Category $category)
    {
        //
        $validator = Validator($request->all() ,
        [
            'name'=>'required|string|min:3',
            'email'=>'required|string|email',
            'age'=>'string',
        ]);

        if(! $validator->fails()){
            //Update
            $category->name = $request->input('name');
            $category->email = $request->input('email');
            $category->age = $request->input('age');
            $Updated = $category->save();
            return response(['status'=>true , 'message'=>$Updated ? 'Updated Successfuly' : 'Updated Failed' , 'Object'=>$category] , $Updated ? Response::HTTP_OK  : Response  ::HTTP_BAD_REQUEST);
        }else{
            return response(['status'=>false, 'message'=>validator()->getMessageBag()->first()] , Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Category $category)
    {
        //
        $deleted = $category->delete();
        return response(['status'=>true , 'message'=>$deleted ? 'deleted Successfuly' : 'Updated delete'] , $deleted ? Response::HTTP_OK  : Response::HTTP_BAD_REQUEST);

    }
}
