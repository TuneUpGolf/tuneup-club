<?php

namespace App\Imports;

use App\Models\Student;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class StudentsImport implements ToModel, WithHeadingRow
{
    public function model(array $row)
    {

        return new Student([
            'name'              => $row['name'],
            'email'             => $row['email'],
            'password'          => Hash::make(Str::random(10)), // Generates a random password
            'country_code'      => $row['country_code'],
            'dial_code'         => $row['dial_code'],
            'phone'             => $row['phone'],
            'type'              => 'Student',
            'created_by'        => Auth::user()->id,
            'email_verified_at' => null,
            'phone_verified_at' => null,

            // Add other fields as necessary
        ]);
    }
}
