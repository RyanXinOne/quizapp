function addQuestion() {
    let questions = document.getElementById('questions');
    // calculate quesId
    let quesId = questions.getElementsByClassName('question').length + 1;
    // create new question element
    let newQuestion = document.createElement('div');
    newQuestion.className = 'question';
    // render question
    let html = '<div class="score"><label> Score:</label> <input type="number" min="0" name="q' + quesId + 'score" value="10" required /></div>';
    html += '<div class="questionText"><label>Question ' + quesId + '</label><textarea name="q' + quesId + '" placeholder="Question Texts" required></textarea></div>';
    html += '<div class="options"></div>';
    html += '<input type="button" value="Add Option" onclick="addOption(' + quesId + ')" />';
    html += '<input type="button" value="Delete Question" onclick="deleteQuestion(' + quesId + ')" />';
    newQuestion.innerHTML = html;
    // add into questions
    if (quesId === 1) {
        questions.innerHTML = '';
    }
    questions.appendChild(newQuestion);
    // add two options
    for (let i = 0; i < 2; i++) {
        addOption(quesId);
    }
}

function addOption(quesId) {
    let question = document.getElementsByClassName('question')[quesId - 1];
    let options = question.getElementsByClassName('options')[0];
    // calculate option id
    let optId = options.getElementsByClassName('option').length + 1;
    // create new option element
    let newOption = document.createElement('div');
    newOption.className = 'option';
    // render option
    let html = '<input type="button" value="-" onclick="deleteOption(' + quesId + ',' + optId + ')" />';
    html += '<input type="radio" name="q' + quesId + 'ans" value="' + optId + '" required />';
    html += '<textarea name="q' + quesId + 'o' + optId + '" placeholder="Option Texts" required></textarea>';
    newOption.innerHTML = html;
    // add into options
    if (optId === 1) {
        options.innerHTML = '';
    }
    options.appendChild(newOption);
}

function deleteQuestion(quesId) {
    // remove question form
    let questions = document.getElementById('questions');
    let question = questions.getElementsByClassName('question')[quesId - 1];
    questions.removeChild(question);

    question = questions.getElementsByClassName('question');
    if (question.length === 0) {
        questions.innerHTML = '<p>There is no question here.</p>';
        return;
    }
    // update question id
    for (let i = quesId - 1; i < question.length; i++) {
        let quesId = i + 1;
        let inputs = question[i].getElementsByTagName('input');
        inputs[0].name = 'q' + quesId + 'score';
        question[i].getElementsByTagName('label')[1].innerText = 'Question ' + quesId;
        question[i].getElementsByTagName('textarea')[0].name = 'q' + quesId;
        inputs[inputs.length - 2].setAttribute('onclick', 'addOption(' + quesId + ')');
        inputs[inputs.length - 1].setAttribute('onclick', 'deleteQuestion(' + quesId + ')');

        let option = question[i].getElementsByClassName('option');
        for (let j = 0; j < option.length; j++) {
            let optId = j + 1;
            option[j].getElementsByTagName('input')[0].setAttribute('onclick', 'deleteOption(' + quesId + ',' + optId + ')');
            option[j].getElementsByTagName('input')[1].name = 'q' + quesId + 'ans';
            option[j].getElementsByTagName('textarea')[0].name = 'q' + quesId + 'o' + optId;
        }
    }
}

function deleteOption(quesId, optId) {
    // remove option form
    let question = document.getElementsByClassName('question')[quesId - 1];
    let options = question.getElementsByClassName('options')[0];
    let option = options.getElementsByClassName('option')[optId - 1];
    options.removeChild(option);

    option = options.getElementsByClassName('option');
    if (option.length === 0) {
        options.innerHTML = '<p>No options available.</p>';
        return;
    }
    // update option id
    for (let i = optId - 1; i < option.length; i++) {
        let optId = i + 1;
        let inputs = option[i].getElementsByTagName('input');
        inputs[0].setAttribute('onclick', 'deleteOption(' + quesId + ',' + optId + ')');
        inputs[1].value = optId;
        option[i].getElementsByTagName('textarea')[0].name = 'q' + quesId + 'o' + optId;
    }
}
