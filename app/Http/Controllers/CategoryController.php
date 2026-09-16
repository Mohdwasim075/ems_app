<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
     public function getcategories(Request $request){
     // Get limit from query string (defaults to 10 if not provided)
    $limit = $request->query('limit', 10);

    // Fetch paginated categories ordered by newest first
    $categories = Category::oldest()->paginate($limit);


    return response()->json([
        'success' => true,
        'message' => 'Event categories fetched successfully!',
        'data'    => $categories->items(), // Array of category records
        'pagination' => [
            'current_page' => $categories->currentPage(),
            'last_page'    => $categories->lastPage(),
            'per_page'     => $categories->perPage(),
            'total'        => $categories->total(),
            'from'         => $categories->firstItem(),
            'to'           => $categories->lastItem(),
        ]
    ], 200);
        
    }

     public function categoryList(){
        $categoryList = Category::all();

        return response()->json([
            'message' => 'category list fetched successfully',
            'data' => $categoryList
        ],200);
    }


      public function show($id){
        

        $category = Category::where('id', intval($id) )->get();

       return response()->json([
        'message' => 'category  fetched',
       'category' => $category,
    ]);
    }


     public function createcategory(Request $request){

       // 1. Validate incoming request
        $validated = $request->validate([
            'name'        => ['required', 'string', 'max:50'],
            'description' => ['nullable', 'string', 'max:255'],
            'is_active'   => ['required', 'boolean']
        ]);

        // 2. Create Category Record
        $category = Category::create([
            'name'        => $validated['name'],
            'description' => $validated['description'],
            'is_active'   => filter_var($validated['is_active'], FILTER_VALIDATE_BOOLEAN) ? 1 : 0
        ]);

        // 3. Return JSON Response for AJAX
        return response()->json([
            'success'  => true,
            'message'  => 'Category created successfully!',
            'category' => $category
        ], 201);
       

    }


    public function updateCategory(Request $request, string $id){

        // dd($request->all());

        //fetch the category model instance

        $category = Category::findOrFail($id);


        //validation

        $validatedAttributes = $request->validate([
            'name' => ['required', 'string', 'max:50'],
            'description' => ['required','string', 'max:50'] ,
            'is_active'  => ['required', 'boolean']
        ],[
            //Custom Error Messages
            'name.required'     => 'Category name is required',
            'name.max'          => 'Category name cannot exceed 50 characters',
            'description.max'   => 'Description cannot exceed 255 characters',
            'is_active.boolean' => 'Invalid status value provided'

        ]);

        //update the category to the database
        $category->update($validatedAttributes);

        //return response
         // 4. Return Standard JSON Response for AJAX
        return response()->json([
            'success' => true,
            'message' => 'category updated successfully!',
            
        ], 200);


    }


     public function deleteCategory(string $id){
        

        //get the category instance from database
        $category = Category::findOrFail($id);

        //checl if the categpry is referencing any other table
        if($category->events()->exists()){
            return response()->json([
                'message' => 'cannot delete category which has events'

            ],409);
            

        }
        $category->delete();

        return response()->json([
            'message' => 'category deleted successfully!'
        ], 200);


    }
}
