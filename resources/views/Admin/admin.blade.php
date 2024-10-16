<x-admineditlay>
    <table class="styled-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Email</th>
                <th>Username</th>
                <th>Role</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($data as $row)
                <tr>
                    <td>{{ $row->id }}</td>
                    <td>{{ $row->email }}</td>
                    <td>{{ $row->username }}</td>
                    <td>{{ $row->role }}</td>
                    <td>
                        <a href="{{ route('edit', ['id' => $row->id]) }}" class="action-link edit-link">
                            <i class="fas fa-edit"></i> Edit
                        </a>

                        <form action="{{ route('delete-user', ['id' => $row->id]) }}" method="POST" class="delete-form">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="action-button delete-button">
                                <i class="fas fa-trash-alt"></i> Hapus
                            </button>
                        </form>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</x-admineditlay>
