<?php

namespace App\Http\Controllers;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class NoteController extends Controller
{
    public function index()
    {
        try {
            $notes = auth()->user()->notes;
            foreach ($notes as $note) {
                $note->images = json_decode($note->images);
            }
            $notes->load(['background']);
            return response()->json([
                'status' => 'success',
                'message' => 'Notes retrieved successfully',
                'data' => $notes
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function show(Request $request, $id)
    {
        try {
            $note = $request->user()->notes()->find($id);
            if (!$note) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Note not found'
                ], 404);
            }
            $note->images = json_decode($note->images);
            $note->load(['background']);
            return response()->json([
                'status' => 'success',
                'message' => 'Note retrieved successfully',
                'data' => $note
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $request->validate([
                'title' => 'required',
                'content' => 'required',
                'images' => 'nullable|array',
                'images.*' => 'nullable|image',
                'is_pinned' => 'boolean|nullable',
                'background_id' => 'integer|nullable|exists:backgrounds,id',
            ]);

            $images = [];
            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $image) {
                    $newName = auth()->user()->name . '-' .  time() . '-' . uniqid() . '-' . rand(1000, 9999) . '.' . $image->getClientOriginalExtension();
                    $image->storeAs('public/notes-images', $newName);
                    $images[] = 'storage/notes-images/' . $newName;
                }
            }

            $note = $request->user()->notes()->create([
                'title' => $request->title,
                'content' => $request->content,
                'images' =>  json_encode($images),
                'is_pinned' => $request->is_pinned ?? false,
                'background_id' => $request->background_id ?? null
            ]);

            $note->images = json_decode($note->images);
            $note->load('background');
            return response()->json([
                'status' => 'success',
                'message' => 'Note created successfully',
                'data' => $note
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 400);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $request->validate([
                'title' => 'required',
                'content' => 'required',
                'images' => 'nullable|array',
                'images.*' => 'nullable|image',
                'is_pinned' => 'boolean|nullable',
                'background_id' => 'integer|nullable|exists:backgrounds,id',
            ]);
            $note = $request->user()->notes()->find($id);
            if (!$note) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Note not found'
                ], 404);
            }

            if ($note->images) {
                $images = json_decode($note->images);
                foreach ($images as $image) {
                    if (file_exists(public_path($image))) {
                        unlink(public_path($image));
                    }
                }
            }

            $images = [];
            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $image) {
                    $newName = auth()->user()->name . '-' .  time() . '-' . uniqid() . '-' . rand(1000, 9999) . '.' . $image->getClientOriginalExtension();
                    $image->storeAs('public/notes-images', $newName);
                    $images[] = 'storage/notes-images/' . $newName;
                }
            }


            $note->update([
                'title' => $request->title,
                'content' => $request->content,
                'images' =>  json_encode($images),
                'is_pinned' => $request->is_pinned ?? false,
                'background_id' => $request->background_id ?? null
            ]);

            $note->images = json_decode($note->images);
            $note->load('background');
            return response()->json([
                'status' => 'success',
                'message' => 'Note updated successfully',
                'data' => $note
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 400);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy(Request $request)
    {
        try {
            $request->validate([
                'id' => 'required|array|exists:notes,id'
            ]);

            $notes = $request->user()->notes()->whereIn('id', $request->id)->get();
            foreach ($notes as $note) {
                $note->images = json_decode($note->images);
                foreach ($note->images as $image) {
                    if (file_exists(public_path($image))) {
                        unlink(public_path($image));
                    }
                }
                $note->delete();
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Note deleted successfully'
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }
    public function softDelete(Request $request)
    {
        try {
            $request->validate([
                'id' => 'required|array|exists:notes,id'
            ]);

            $notes = $request->user()->notes()->whereIn('id', $request->id)->get();
            foreach ($notes as $note) {
                $note->update([
                    'is_deleted' => !$note->is_deleted
                ]);
                if ($note->is_deleted) {
                    $message = 'moved to trash';
                } else {
                    $message = 'restored';
                }
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Note ' . $message
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function pin(Request $request)
    {
        try {
            $request->validate([
                'id' => 'required|array|exists:notes,id'
            ]);
            $notes = $request->user()->notes()->whereIn('id', $request->id)->get();
            foreach ($notes as $note) {
                $note->update([
                    'is_pinned' => !$note->is_pinned
                ]);
                if ($note->is_pinned) {
                    $message = 'pinned';
                } else {
                    $message = 'unpinned';
                }
            }
            return response()->json([
                'status' => 'success',
                'message' => 'Note ' . $message
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
