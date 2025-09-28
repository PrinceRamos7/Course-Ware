<?php
// =====================================================================
// 1. PHP LOGIC (Database Connection, Pagination, Search, and Fetch)
// =====================================================================
require __DIR__ . '/../config.php';

// Pagination & Search
$limit = 5; 
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$offset = ($page - 1) * $limit;

// Module filter
$module_id = isset($_GET['module_id']) ? (int)$_GET['module_id'] : 0;

// --- Fetch Current Module Title (for header context) ---
$current_module_title = 'All Modules';
if ($module_id > 0) {
	$module_stmt = $conn->prepare("SELECT title FROM modules WHERE id = :id");
	$module_stmt->execute([':id' => $module_id]);
	$result = $module_stmt->fetch(PDO::FETCH_ASSOC);
	if ($result) {
		$current_module_title = htmlspecialchars($result['title']);
	}
}

// Count total topics
$count_sql = "SELECT COUNT(*) AS total FROM topics t 
			  LEFT JOIN modules m ON t.module_id = m.id";
$where = [];
$params = [];

if ($module_id > 0) {
	$where[] = "t.module_id = :module_id";
	$params[':module_id'] = $module_id;
}
if ($search !== '') {
	$where[] = "(t.title LIKE :search OR t.description LIKE :search OR m.title LIKE :search)";
	$params[':search'] = "%$search%";
}
if ($where) $count_sql .= " WHERE " . implode(" AND ", $where);

$stmt = $conn->prepare($count_sql);
$stmt->execute($params);
$total_topics = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
$total_pages = ceil($total_topics / $limit);

// Fetch topics with module title
$topics_sql = "SELECT t.*, m.title AS module_title 
			   FROM topics t 
			   LEFT JOIN modules m ON t.module_id = m.id";
if ($where) $topics_sql .= " WHERE " . implode(" AND ", $where);
$topics_sql .= " ORDER BY t.id DESC LIMIT :offset, :limit";

$stmt = $conn->prepare($topics_sql);
foreach ($params as $k => $v) $stmt->bindValue($k, $v);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
$stmt->execute();
$topics_result = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch modules for dropdown (when adding/editing topics)
$modules = $conn->query("SELECT id, title FROM modules ORDER BY title ASC")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title>Admin Topics - FixLearn</title>
<link rel="stylesheet" href="../output.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"/>
<style>
	/* ===================================================================== */
	/* 2. EMBEDDED STYLES (Theming from Dashboard) */
	/* ===================================================================== */
	
	/* General Layout Class for consistency */
	.dashboard-container {
		padding: 1.5rem 2rem;
		gap: 1.5rem;
	}
	
	/* Header Styling matching the Dashboard */
	.header-bg { 
		background-color: var(--color-card-bg); 
		border-bottom: 2px solid var(--color-sidebar-border); 
	}

	/* Card Styling for main table wrapper */
	.card-bg {
		background-color: var(--color-card-bg);
		border: 1px solid var(--color-card-border);
	}
	
	/* Input field styling in modals/tables */
	.input-themed {
		background-color: var(--color-input-bg);
		border: 1px solid var(--color-input-border);
		color: var(--color-input-text);
		box-shadow: inset 0 1px 3px rgba(0,0,0,0.06);
	}
	.input-themed:focus {
		border-color: var(--color-icon); /* ISU Yellow focus */
		box-shadow: 0 0 0 2px rgba(234, 179, 8, 0.5); 
		outline: none;
	}

	/* Side Modal Styles */
	.sidebar-modal { 
		position: fixed; 
		top: 0; 
		right: 0; 
		height: 100%; 
		width: 100%; 
		max-width: 32rem; 
		background: var(--color-popup-content-bg); 
		z-index: 50; 
		/* The initial off-screen position */
		transform: translateX(100%); 
		transition: transform 0.3s ease; 
		overflow-y: auto; 
		box-shadow: -4px 0 12px rgba(0,0,0,0.2); 
		padding: 1.5rem;
	}
	/* The class added by JS to trigger the slide-in */
	.sidebar-modal.show { 
		transform: translateX(0); 
	}
	.modal-overlay { 
		position: fixed; 
		inset: 0; 
		background: var(--color-popup-bg); 
		z-index: 40; 
	}

	/* General Animation */
	.fade-slide { opacity: 0; transform: translateY(20px); transition: opacity .8s ease, transform .8s ease; }
	.fade-slide.show { opacity: 1; transform: translateY(0); }
</style>
</head>
<body class="bg-[var(--color-main-bg)] min-h-screen flex text-[var(--color-text)]">
<?php include __DIR__ . '/sidebar.php'; ?>

<div class="flex-1 flex flex-col overflow-y-auto">

	<?php include 'header.php';
	renderHeader("ISU Admin Topics")
	?>

	<div class="flex flex-col sm:flex-row justify-between items-center m-6 gap-3">
		<form method="GET" class="flex w-full sm:w-auto gap-2">
			<input type="hidden" name="module_id" value="<?= $module_id; ?>">
			<input 
				type="text" 
				name="search" 
				value="<?= htmlspecialchars($search); ?>" 
				placeholder="Search topics..." 
				class="w-full sm:w-72 px-4 py-2 border rounded-lg shadow-inner input-themed focus:ring-[var(--color-heading)] focus:ring-2 transition placeholder-[var(--color-input-placeholder)]"
			>
			<button 
				type="submit" 
				class="px-5 py-2 bg-[var(--color-button-primary)] text-white font-bold rounded-lg shadow-md hover:bg-[var(--color-button-primary-hover)] active:scale-95 transition"
			>
				<i class="fas fa-search"></i>
			</button>
		</form>

		<button 
			id="openAddTopic" 
			class="flex items-center gap-2 px-5 py-2 w-full sm:w-auto bg-[var(--color-heading)] text-white font-bold rounded-lg shadow-lg hover:bg-[var(--color-button-primary-hover)] transition transform hover:scale-[1.02]"
		>
			<i class="fas fa-plus-circle"></i> Add Topic
		</button>
	</div>

	<div class="card-bg pb-2 rounded-xl shadow-xl border border-[var(--color-card-border)] m-6 overflow-hidden fade-slide">
		<div class="overflow-x-auto">
			<table class="w-full text-left border-collapse">
				<thead>
					<tr class="bg-[var(--color-card-section-bg)] text-[var(--color-text-on-section)] text-sm uppercase tracking-wider border-b border-[var(--color-card-section-border)]">
						<th class="p-4 rounded-tl-xl font-bold">#</th>
						<th class="p-4 font-bold">Title</th>
						<th class="p-4 font-bold">Module</th>
						<th class="p-4 font-bold">Description</th>
						<th class="p-4 font-bold text-center">Minutes</th>
						<th class="p-4 font-bold text-center">XP</th>
						<th class="p-4 rounded-tr-xl font-bold text-center">Actions</th>
					</tr>
				</thead>
				<tbody class="divide-y divide-[var(--color-card-border)]">
					<?php if (!empty($topics_result)): ?>
						<?php $i = $offset + 1; foreach ($topics_result as $row): ?>
							<tr class="hover:bg-yellow-50/10 transition duration-150">
								<td class="p-4 text-[var(--color-text-secondary)]"><?= $i++; ?></td>
								<td class="p-4 font-semibold text-[var(--color-heading-secondary)]"><?= htmlspecialchars($row['title']); ?></td>
								<td class="p-4 text-[var(--color-text-secondary)]"><?= htmlspecialchars($row['module_title']); ?></td>
								<td class="p-4 text-[var(--color-text-secondary)] italic">
									<?= htmlspecialchars(substr($row['description'], 0, 60)); ?><?= strlen($row['description']) > 60 ? '...' : ''; ?>
								</td>
								<td class="p-4 text-center text-[var(--color-heading)] font-bold"><?= $row['estimated_minute']; ?></td>
								<td class="p-4 text-center text-[var(--color-heading-secondary)] font-bold"><?= $row['total_exp']; ?></td>
								<td class="p-4 flex justify-center gap-2">
									
									<a href="assessment.php?topic_id=<?= $row['id']; ?>&module_id=<?= $row['module_id']; ?>" 
										class="px-4 py-2 text-sm font-semibold bg-purple-100 text-purple-700 rounded-full hover:bg-purple-200 transition shadow-sm" title="Manage Assessments">
										<i class="fas fa-clipboard-question mr-1"></i> Assess
									</a>

									<button 
										class="px-3 py-2 text-sm font-medium bg-green-100 text-green-700 rounded-full hover:bg-green-200 transition shadow-sm editTopicBtn"
										data-id="<?= $row['id']; ?>"
										data-title="<?= htmlspecialchars($row['title']); ?>"
										data-description="<?= htmlspecialchars($row['description']); ?>"
										data-content="<?= htmlspecialchars($row['content']); ?>"
										data-module="<?= $row['module_id']; ?>"
										data-minute="<?= $row['estimated_minute']; ?>"
										data-exp="<?= $row['total_exp']; ?>" title="Edit Topic">
										<i class="fas fa-edit"></i>
									</button>

									<a href="topics_code.php?action=delete&id=<?= $row['id']; ?>&module_id=<?= $row['module_id']; ?>"
										onclick="return confirm('Are you sure you want to delete this topic?');"
										class="px-3 py-2 text-sm font-medium bg-red-100 text-red-600 rounded-full hover:bg-red-200 transition shadow-sm" title="Delete Topic">
										<i class="fas fa-trash-alt"></i>
									</a>

								</td>
							</tr>
						<?php endforeach; ?>
					<?php else: ?>
						<tr>
							<td colspan="7" class="p-6 text-center text-[var(--color-text-secondary)] font-medium"><i class="fas fa-exclamation-circle mr-2"></i> No topics found.</td>
						</tr>
					<?php endif; ?>
				</tbody>
			</table>
		</div>

		<?php if ($total_pages > 1): ?>
			<div class="mt-8 flex justify-center flex-wrap gap-2">
				<?php for($p=1; $p<=$total_pages; $p++): ?>
					<a href="?page=<?= $p; ?>&search=<?= urlencode($search); ?>&module_id=<?= $module_id; ?>" 
						class="px-5 py-2 rounded-full text-sm font-semibold shadow-md transition transform hover:scale-105 
						<?= $p==$page 
							? 'bg-[var(--color-heading-secondary)] text-white' 
							: 'bg-[var(--color-button-secondary)] text-[var(--color-button-secondary-text)] hover:bg-yellow-200'; ?>">
						<?= $p; ?>
					</a>
				<?php endfor; ?>
			</div>
		<?php endif; ?>
	</div>

	<div class="mt-6 text-right">
		<a href="module.php" 
			class="bg-gray-500 text-white px-4 py-2 rounded-lg hover:bg-gray-600 transition font-medium shadow-md">
			<i class="fas fa-arrow-left mr-2"></i> Back to Modules
		</a>
	</div>

	<div id="addTopicModal" class="fixed inset-0 z-50 hidden">
		<div class="modal-overlay" id="closeAddTopic"></div>
		<div class="sidebar-modal" id="addModalContent"> 
			<h3 class="text-2xl font-bold mb-6 text-[var(--color-heading)]"><i class="fas fa-plus-square mr-2 text-[var(--color-icon)]"></i> Add New Topic</h3>
			<form method="POST" action="topics_code.php">
				<input type="hidden" name="action" value="add">
				
				<div class="mb-4">
					<label class="block font-semibold mb-1 text-[var(--color-text)]">Title</label>
					<input type="text" name="title" class="w-full p-3 rounded-lg input-themed" required>
				</div>
				
				<div class="mb-4">
					<label class="block font-semibold mb-1 text-[var(--color-text)]">Description</label>
					<textarea name="description" class="w-full p-3 rounded-lg input-themed" rows="3"></textarea>
				</div>
				
				<div class="mb-4">
					<label class="block font-semibold mb-1 text-[var(--color-text)]">Content (HTML/Text)</label>
					<textarea name="content" class="w-full p-3 rounded-lg input-themed" rows="4"></textarea>
				</div>
				
				<div class="mb-4">
					<label class="block font-semibold mb-1 text-[var(--color-text)]">Module</label>
					<select name="module_id" class="w-full p-3 rounded-lg input-themed" required>
						<option value="">-- Select Module --</option>
						<?php foreach ($modules as $module): ?>
							<option value="<?= $module['id']; ?>" <?= $module['id'] == $module_id ? 'selected' : ''; ?>>
								<?= htmlspecialchars($module['title']); ?>
							</option>
						<?php endforeach; ?>
					</select>
				</div>
				
				<div class="mb-4">
					<label class="block font-semibold mb-1 text-[var(--color-text)]">Estimated Minutes</label>
					<input type="number" name="estimated_minute" min="0" class="w-full p-3 rounded-lg input-themed">
				</div>
				
				<div class="mb-6">
					<label class="block font-semibold mb-1 text-[var(--color-text)]">XP (Experience Points)</label>
					<input type="number" name="total_exp" min="0" class="w-full p-3 rounded-lg input-themed">
				</div>
				
				<div class="flex justify-end space-x-3 pt-4 border-t border-[var(--color-card-border)]">
					<button type="button" id="cancelAddTopic" class="px-5 py-2 rounded-lg bg-gray-200 hover:bg-gray-300 transition font-medium">Cancel</button>
					<button type="submit" class="px-5 py-2 rounded-lg bg-[var(--color-button-primary)] text-white hover:bg-[var(--color-button-primary-hover)] transition font-bold shadow-md">
						<i class="fas fa-check-circle mr-1"></i> Add Topic
					</button>
				</div>
			</form>
		</div>
	</div>

	<div id="editTopicModal" class="fixed inset-0 z-50 hidden">
		<div class="modal-overlay" id="closeEditTopic"></div>
		<div class="sidebar-modal" id="editModalContent">
			<h3 class="text-2xl font-bold mb-6 text-[var(--color-heading)]"><i class="fas fa-pen-to-square mr-2 text-[var(--color-icon)]"></i> Edit Topic</h3>
			<form method="POST" action="topics_code.php">
				<input type="hidden" name="action" value="edit">
				<input type="hidden" name="id" id="editTopicId">
				
				<div class="mb-4">
					<label class="block font-semibold mb-1 text-[var(--color-text)]">Title</label>
					<input type="text" name="title" id="editTopicTitle" class="w-full p-3 rounded-lg input-themed" required>
				</div>
				
				<div class="mb-4">
					<label class="block font-semibold mb-1 text-[var(--color-text)]">Description</label>
					<textarea name="description" id="editTopicDescription" class="w-full p-3 rounded-lg input-themed" rows="3"></textarea>
				</div>
				
				<div class="mb-4">
					<label class="block font-semibold mb-1 text-[var(--color-text)]">Content (HTML/Text)</label>
					<textarea name="content" id="editTopicContent" class="w-full p-3 rounded-lg input-themed" rows="4"></textarea>
				</div>
				
				<div class="mb-4">
					<label class="block font-semibold mb-1 text-[var(--color-text)]">Module</label>
					<select name="module_id" id="editTopicModule" class="w-full p-3 rounded-lg input-themed" required>
						<?php foreach ($modules as $module): ?>
							<option value="<?= $module['id']; ?>"><?= htmlspecialchars($module['title']); ?></option>
						<?php endforeach; ?>
					</select>
				</div>
				
				<div class="mb-4">
					<label class="block font-semibold mb-1 text-[var(--color-text)]">Estimated Minutes</label>
					<input type="number" name="estimated_minute" min="0" id="editTopicMinute" class="w-full p-3 rounded-lg input-themed">
				</div>
				
				<div class="mb-6">
					<label class="block font-semibold mb-1 text-[var(--color-text)]">XP (Experience Points)</label>
					<input type="number" name="total_exp" min="0" id="editTopicExp" class="w-full p-3 rounded-lg input-themed">
				</div>
				
				<div class="flex justify-end space-x-3 pt-4 border-t border-[var(--color-card-border)]">
					<button type="button" id="cancelEditTopic" class="px-5 py-2 rounded-lg bg-gray-200 hover:bg-gray-300 transition font-medium">Cancel</button>
					<button type="submit" class="px-5 py-2 rounded-lg bg-green-600 text-white hover:bg-green-700 transition font-bold shadow-md">
						<i class="fas fa-save mr-1"></i> Save Changes
					</button>
				</div>
			</form>
		</div>
	</div>

	<script>
		// --- Utility Functions for Modals (FIXED) ---
		function setupSidebarModal(openBtnId, modalId, contentId, closeOverlayId, cancelBtnId, initCallback = null) {
			const openBtn = openBtnId ? document.getElementById(openBtnId) : null;
			const modal = document.getElementById(modalId);
			const modalContent = document.getElementById(contentId);
			const closeOverlay = document.getElementById(closeOverlayId);
			const cancelBtn = document.getElementById(cancelBtnId);

			function openModal() { 
				// 1. Show the modal container (removes 'hidden')
				modal.classList.remove('hidden'); 
				// 2. Add 'show' class to trigger the CSS transition (transform: translateX(0))
				setTimeout(() => modalContent.classList.add('show'), 10);
				if (initCallback) initCallback();
			}

			function closeModal() { 
				// 1. Remove 'show' class to trigger the CSS transition (slides back to transform: translateX(100%))
				modalContent.classList.remove('show'); 
				// 2. Hide the modal container after the transition finishes (300ms)
				setTimeout(() => modal.classList.add('hidden'), 300);
			}

			if(openBtn) openBtn.addEventListener('click', openModal);
			if(closeOverlay) closeOverlay.addEventListener('click', closeModal);
			if(cancelBtn) cancelBtn.addEventListener('click', closeModal);
			
			return { openModal, closeModal };
		}
		
		// Fade animation for main table
		document.querySelectorAll('.fade-slide').forEach((el, i) => 
			setTimeout(() => el.classList.add('show'), i * 150)
		);

		// Add Modal Setup
		// The Add Modal should now work because of the fix in setupSidebarModal
		const { openModal: openAdd, closeModal: closeAdd } = setupSidebarModal('openAddTopic', 'addTopicModal', 'addModalContent', 'closeAddTopic', 'cancelAddTopic');

		// Edit Modal Setup
		const { closeModal: closeEdit } = setupSidebarModal(null, 'editTopicModal', 'editModalContent', 'closeEditTopic', 'cancelEditTopic');
		const editBtns = document.querySelectorAll('.editTopicBtn');
		const editModal = document.getElementById('editTopicModal');
		const editModalContent = document.getElementById('editModalContent');

		editBtns.forEach(btn => {
			btn.addEventListener('click', e => {
				e.preventDefault();
				// Populate form fields
				document.getElementById('editTopicId').value = btn.dataset.id;
				document.getElementById('editTopicTitle').value = btn.dataset.title;
				document.getElementById('editTopicDescription').value = btn.dataset.description;
				document.getElementById('editTopicContent').value = btn.dataset.content;
				document.getElementById('editTopicModule').value = btn.dataset.module;
				document.getElementById('editTopicMinute').value = btn.dataset.minute;
				document.getElementById('editTopicExp').value = btn.dataset.exp;
				
				// Open modal manually since it's triggered by a button inside a loop
				editModal.classList.remove('hidden');
				setTimeout(() => editModalContent.classList.add('show'), 10);
			});
		});
	</script>
	</body>
</html>